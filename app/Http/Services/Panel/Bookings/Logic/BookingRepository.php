<?php

namespace App\Http\Services\Panel\Bookings\Logic;

use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BookingRepository
{
    public function __construct(private EntityScope $scope) {}

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function paginate(?string $search, ?string $status, ?int $officeId, int $perPage = 15): LengthAwarePaginator
    {
        $agg = \App\Http\Core\GeoServices\ShardAggregator::isActive();

        $columns = [
            'bookings.id',
            'bookings.status',
            'bookings.totalAmount',
            'bookings.paymentStatus',
            'bookings.paymentType',
            'bookings.created_at',
            'bookings.is_scheduled',
            'bookings.officeId',
            'offices.officeName as office_name',
            DB::raw("TRIM(CONCAT(COALESCE(users.firstName, ''), ' ', COALESCE(users.lastName, ''))) as customer"),
            DB::raw("TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) as driver"),
        ];

        if ($agg) {
            $columns[] = 'bookings._country';
            $columns[] = 'bookings._shard';
        }

        $query = Booking::on($this->connection())
            ->leftJoin('users', 'bookings.userId', '=', 'users.id')
            ->leftJoin('drivers', function ($join) use ($agg) {
                $join->on('bookings.driverId', '=', 'drivers.id');
                if ($agg) {
                    $join->on('bookings._shard', '=', 'drivers._shard');
                }
            })
            ->leftJoin('offices', function ($join) use ($agg) {
                $join->on('bookings.officeId', '=', 'offices.id');
                if ($agg) {
                    $join->on('bookings._shard', '=', 'offices._shard');
                }
            })
            ->select($columns)
            ->when($status, fn (Builder $q) => $q->where('bookings.status', $status))
            ->when($officeId, fn (Builder $q) => $q->where('bookings.officeId', $officeId))
            ->when($search, function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('bookings.id', $search)
                        ->orWhere('users.firstName', 'like', "%{$search}%")
                        ->orWhere('users.lastName', 'like', "%{$search}%")
                        ->orWhere('drivers.firstName', 'like', "%{$search}%")
                        ->orWhere('drivers.lastName', 'like', "%{$search}%");
                });
            })
            ->latest('bookings.id');

        return $query->paginate($perPage)->withQueryString();
    }

    public const COLUMN_STATUSES = [
        'upcoming'  => [BookingStatus::SCHEDULED, BookingStatus::PENDING_ACCEPTANCE, BookingStatus::MATCHING, BookingStatus::CONFIRMED],
        'active'    => [BookingStatus::ASSIGNED, BookingStatus::ARRIVING, BookingStatus::ARRIVED, BookingStatus::ON_TRIP],
        'completed' => [BookingStatus::COMPLETED],
        'cancelled' => [BookingStatus::CANCELLED, BookingStatus::REJECTED, BookingStatus::DECLINED, BookingStatus::NO_DRIVER_EXPIRED],
    ];

    private function scheduledSelect(): array
    {
        return [
            'bookings.id',
            'bookings.status',
            'bookings.scheduled_time',
            'bookings.totalAmount',
            'bookings.paymentType',
            'bookings.paymentStatus',
            'bookings.distance',
            'bookings.startAddress',
            'bookings.endAddress',
            'bookings.driverId',
            'bookings.officeId',
            'offices.officeName as office_name',
            DB::raw("TRIM(CONCAT(COALESCE(users.firstName, ''), ' ', COALESCE(users.lastName, ''))) as customer"),
            DB::raw('users.phoneNumber as customer_phone'),
            DB::raw("TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) as driver"),
            DB::raw('drivers.phoneNumber as driver_phone'),
        ];
    }

    public static function groupOf(?string $status): string
    {
        foreach (self::COLUMN_STATUSES as $key => $statuses) {
            if (in_array($status, $statuses, true)) {
                return $key;
            }
        }

        return 'upcoming';
    }

    public function scheduledData(string $group, ?string $date, ?int $driverId, ?int $officeId, int $page, int $perPage = 20): LengthAwarePaginator
    {
        // Reads the APP pipeline (RideBooking, per-shard) — a scheduled ride is one
        // with `scheduled_at` set. users is on the GLOBAL connection so it can't be
        // joined; customers are hydrated separately. Statuses map to the board's
        // upcoming/active/completed/cancelled columns via COLUMN_STATUSES.
        $statuses = self::COLUMN_STATUSES[$group] ?? self::COLUMN_STATUSES['upcoming'];
        $isPast   = in_array($group, ['completed', 'cancelled'], true);
        $conn     = $this->connection();

        $query = RideBooking::on($conn)
            ->whereNotNull('scheduled_at')
            ->whereIn('status', $statuses)
            ->when($officeId, fn (Builder $q) => $q->where('office_id', $officeId))
            ->when($driverId, fn (Builder $q) => $q->where('driver_id', $driverId));

        $this->applyRideWindow($query, $date, $isPast);

        $paginator = $query
            ->orderBy('scheduled_at', $isPast ? 'desc' : 'asc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        $rows = $this->hydrateScheduledRows($paginator->getCollection(), $conn, $group, $date);
        $paginator->setCollection($rows);

        return $paginator;
    }

    public function scheduledStats(string $group, ?string $date, ?int $driverId, ?int $officeId): array
    {
        $statuses = self::COLUMN_STATUSES[$group] ?? self::COLUMN_STATUSES['upcoming'];
        $isPast   = in_array($group, ['completed', 'cancelled'], true);

        $query = RideBooking::on($this->connection())
            ->whereNotNull('scheduled_at')
            ->whereIn('status', $statuses)
            ->when($officeId, fn (Builder $q) => $q->where('office_id', $officeId))
            ->when($driverId, fn (Builder $q) => $q->where('driver_id', $driverId));

        $this->applyRideWindow($query, $date, $isPast);

        $agg = $query->selectRaw('COUNT(*) as c, COALESCE(SUM(total_minor), 0) as revenue, COALESCE(SUM(distance_m), 0) as distance')->first();

        return [
            'trips'    => (int) ($agg->c ?? 0),
            'revenue'  => (float) ($agg->revenue ?? 0) / 100,
            'distance' => (float) ($agg->distance ?? 0) / 1000,
        ];
    }

    public function findScheduledRow(int $id)
    {
        $conn = $this->connection();
        $booking = RideBooking::on($conn)->whereKey($id)->first();

        if ($booking === null) {
            return null;
        }

        return $this->hydrateScheduledRows(collect([$booking]), $conn, self::scheduledGroupOf((string) $booking->status), null)->first();
    }

    /** Turn RideBooking models into flat rows the scheduled presenter reads. */
    private function hydrateScheduledRows($bookings, ?string $conn, string $group, ?string $date)
    {
        if ($bookings->isEmpty()) {
            return collect();
        }

        $users = User::query()
            ->whereIn('id', $bookings->pluck('user_id')->filter()->unique()->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber'])->keyBy('id');

        $drivers = Driver::on($conn)
            ->whereIn('id', $bookings->pluck('driver_id')->filter()->unique()->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber'])->keyBy('id');

        $offices = Office::on($conn)
            ->whereIn('id', $bookings->pluck('office_id')->filter()->unique()->all())
            ->get(['id', 'officeName'])->keyBy('id');

        return $bookings->map(function (RideBooking $b) use ($users, $drivers, $offices, $date) {
            $u = $users->get($b->user_id);
            $d = $b->driver_id ? $drivers->get($b->driver_id) : null;
            $o = $b->office_id ? $offices->get($b->office_id) : null;
            $when = $b->scheduled_at instanceof Carbon ? $b->scheduled_at : ($b->scheduled_at ? Carbon::parse($b->scheduled_at) : null);
            $status = (string) $b->status;

            $row = new \stdClass();
            $row->id             = (int) $b->id;
            $row->isRide         = true;
            $row->status         = $status;
            $row->statusLabel    = self::scheduledStatusLabel($status);
            $row->group          = self::scheduledGroupOf($status);
            $row->scheduled_time = $when;
            $row->period         = $this->periodFor($when, $date);
            $row->startAddress   = (string) ($b->pickup_title ?? '');
            $row->endAddress     = (string) ($b->dropoff_title ?? '');
            $row->totalAmount    = ((int) ($b->total_minor ?? 0)) / 100;
            $row->distance       = ((float) ($b->distance_m ?? 0)) / 1000;
            $row->paymentType    = $b->payment_method;
            $row->paymentStatus  = null;
            $row->office_name    = $o?->officeName;
            $row->customer       = $u ? trim(($u->firstName ?? '') . ' ' . ($u->lastName ?? '')) : '';
            $row->customer_phone = $u?->phoneNumber;
            $row->driverId       = $b->driver_id ? (int) $b->driver_id : null;
            $row->driver         = $d ? trim(($d->firstName ?? '') . ' ' . ($d->lastName ?? '')) : '';
            $row->driver_phone   = $d?->phoneNumber;

            return $row;
        });
    }

    public static function scheduledGroupOf(string $status): string
    {
        foreach (self::COLUMN_STATUSES as $key => $statuses) {
            if (in_array($status, $statuses, true)) {
                return $key;
            }
        }

        return 'upcoming';
    }

    private static function scheduledStatusLabel(string $status): string
    {
        return match ($status) {
            BookingStatus::SCHEDULED         => textByLanguage('مجدولة', 'Scheduled'),
            BookingStatus::PENDING_ACCEPTANCE => textByLanguage('بانتظار قبول المكتب', 'Awaiting office'),
            BookingStatus::CONFIRMED         => textByLanguage('مؤكّدة', 'Confirmed'),
            BookingStatus::MATCHING          => textByLanguage('جارٍ البحث عن سائق', 'Searching for driver'),
            BookingStatus::ASSIGNED          => textByLanguage('أُسند سائق', 'Driver assigned'),
            BookingStatus::ARRIVING          => textByLanguage('السائق في الطريق', 'Driver on the way'),
            BookingStatus::ARRIVED           => textByLanguage('بانتظار الراكب', 'Awaiting passenger'),
            BookingStatus::ON_TRIP           => textByLanguage('انطلقت الرحلة', 'Trip started'),
            BookingStatus::COMPLETED         => textByLanguage('اكتملت', 'Completed'),
            BookingStatus::CANCELLED         => textByLanguage('ملغاة', 'Cancelled'),
            BookingStatus::REJECTED, BookingStatus::DECLINED => textByLanguage('مرفوضة', 'Rejected'),
            BookingStatus::NO_DRIVER_EXPIRED => textByLanguage('لا سائق متاح', 'No driver'),
            default                          => $status,
        };
    }

    private function applyRideWindow(Builder $query, ?string $date, bool $isPast): void
    {
        if ($date) {
            $query->whereDate('scheduled_at', $date);

            return;
        }

        if ($isPast) {
            $query->whereBetween('scheduled_at', [now()->subDays(7)->startOfDay(), now()->endOfDay()]);

            return;
        }

        $query->whereBetween('scheduled_at', [now()->startOfDay(), now()->addDay()->endOfDay()]);
    }

    private function periodFor($scheduledTime, ?string $date): string
    {
        if ($scheduledTime === null) {
            return 'week';
        }

        $t = $scheduledTime instanceof Carbon ? $scheduledTime : Carbon::parse($scheduledTime);

        if ($date) {
            $hour = (int) $t->format('H');

            return $hour < 12 ? 'morning' : ($hour < 18 ? 'noon' : 'night');
        }

        if ($t->isToday()) {
            return 'today';
        }

        if ($t->isTomorrow()) {
            return 'tomorrow';
        }

        return 'week';
    }

    public function findOrFail(int $id): Booking
    {
        return Booking::on($this->connection())->findOrFail($id);
    }

    public function details(Booking $booking): array
    {
        $conn = $this->connection();

        return [
            'customer'   => $booking->userId ? User::query()->find($booking->userId) : null,
            'driver'     => $booking->driverId ? Driver::on($conn)->find($booking->driverId) : null,
            'office'     => $booking->officeId ? Office::on($conn)->find($booking->officeId) : null,
            'subService' => $booking->subServiceId ? SubService::on($conn)->find($booking->subServiceId) : null,
        ];
    }

    public function updateStatus(Booking $booking, string $status): Booking
    {
        $booking->status = $status;
        $booking->save();

        return $booking;
    }
}
