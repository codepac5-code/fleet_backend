<?php

namespace App\Http\Services\Panel\Drivers\Logic;

use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\Office;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DriverProfile
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    private function db()
    {
        return DB::connection($this->connection());
    }

    public function overview(Driver $driver): array
    {
        // App-era trips live in `ride_bookings` (the legacy `bookings` table
        // stopped filling). Money is stored in MINOR units → ÷100 for display.
        $done = BookingStatus::COMPLETED;
        $today = Carbon::today();
        $week = Carbon::now()->startOfWeek();
        $month = Carbon::now()->startOfMonth();

        $row = $this->db()->table('ride_bookings')
            ->where('driver_id', $driver->id)
            ->selectRaw(
                'COUNT(*) AS all_trips,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS total_trips,
                 SUM(CASE WHEN status = ? THEN total_minor ELSE 0 END) AS total_revenue,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_today,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN total_minor ELSE 0 END) AS rev_today,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_week,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN total_minor ELSE 0 END) AS rev_week,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_month,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN total_minor ELSE 0 END) AS rev_month',
                [$done, $done, $done, $today, $done, $today, $done, $week, $done, $week, $done, $month, $done, $month]
            )->first();

        $money = fn ($minor) => (float) ($minor ?? 0) / 100;

        return [
            'allTrips'     => (int) ($row->all_trips ?? 0),
            'totalTrips'   => (int) ($row->total_trips ?? 0),
            'totalRevenue' => $money($row->total_revenue ?? 0),
            'periods'      => [
                'today' => ['trips' => (int) ($row->trips_today ?? 0), 'revenue' => $money($row->rev_today ?? 0)],
                'week'  => ['trips' => (int) ($row->trips_week ?? 0), 'revenue' => $money($row->rev_week ?? 0)],
                'month' => ['trips' => (int) ($row->trips_month ?? 0), 'revenue' => $money($row->rev_month ?? 0)],
            ],
        ];
    }

    public function statsForDate(Driver $driver, Carbon $date): array
    {
        $row = $this->db()->table('ride_bookings')
            ->where('driver_id', $driver->id)
            ->where('status', BookingStatus::COMPLETED)
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->selectRaw('COUNT(*) AS trips, COALESCE(SUM(total_minor), 0) AS revenue')
            ->first();

        return ['trips' => (int) $row->trips, 'revenue' => (float) $row->revenue / 100];
    }

    public function vehicle(Driver $driver): ?Vehicle
    {
        if (! $driver->vehicleId) {
            return null;
        }

        return Vehicle::on($this->connection())->find($driver->vehicleId);
    }

    public function ratingSummary(Driver $driver): array
    {
        // App ratings live in `ride_ratings` (ratee_type/ratee_id + integer stars),
        // not the legacy `ratings` table (rated_person_type/rating).
        $row = $this->db()->table('ride_ratings')
            ->where('ratee_type', 'driver')
            ->where('ratee_id', $driver->id)
            ->selectRaw(
                'COUNT(*) AS cnt, COALESCE(AVG(stars), 0) AS avg,
                 SUM(CASE WHEN stars >= 5 THEN 1 ELSE 0 END) AS s5,
                 SUM(CASE WHEN stars = 4 THEN 1 ELSE 0 END) AS s4,
                 SUM(CASE WHEN stars = 3 THEN 1 ELSE 0 END) AS s3,
                 SUM(CASE WHEN stars = 2 THEN 1 ELSE 0 END) AS s2,
                 SUM(CASE WHEN stars <= 1 THEN 1 ELSE 0 END) AS s1'
            )->first();

        $cnt = (int) $row->cnt;

        return [
            'count'        => $cnt,
            'average'      => round((float) $row->avg, 2),
            'distribution' => [
                5 => (int) $row->s5,
                4 => (int) $row->s4,
                3 => (int) $row->s3,
                2 => (int) $row->s2,
                1 => (int) $row->s1,
            ],
        ];
    }

    public function ratingsFeed(Driver $driver, int $perPage = 8): LengthAwarePaginator
    {
        $page = $this->db()->table('ride_ratings')
            ->where('ratee_type', 'driver')
            ->where('ratee_id', $driver->id)
            ->select(['id', 'stars AS rating', 'comment AS description', 'rater_type', 'rater_id', 'booking_id AS orderId', 'created_at'])
            ->orderByDesc('id')
            ->paginate($perPage);

        $this->resolveRaters($page->getCollection());

        return $page;
    }

    private function resolveRaters($items): void
    {
        $byType = [];
        foreach ($items as $r) {
            if ($r->rater_type) {
                $byType[$r->rater_type][] = $r->rater_id;
            }
        }

        $names = [];
        foreach ($byType as $type => $ids) {
            $names[$type] = $this->resolveNames($type, array_values(array_unique(array_filter($ids))));
        }

        foreach ($items as $r) {
            $r->rater_name = $names[$r->rater_type][$r->rater_id] ?? null;
        }
    }

    private function resolveNames(string $type, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $conn = $this->connection();

        // ride_ratings stores rater_type as a short string ('user'/'office'/
        // 'driver'); the legacy table used class names — handle both.
        return match ($type) {
            'user', User::class => User::query()->whereIn('id', $ids)->get(['id', 'firstName', 'lastName'])
                ->mapWithKeys(fn ($u) => [$u->id => trim($u->firstName . ' ' . $u->lastName)])->all(),
            'office', Office::class => Office::on($conn)->whereIn('id', $ids)->pluck('officeName', 'id')->all(),
            'driver', Driver::class => Driver::on($conn)->whereIn('id', $ids)->get(['id', 'firstName', 'lastName'])
                ->mapWithKeys(fn ($d) => [$d->id => trim($d->firstName . ' ' . $d->lastName)])->all(),
            default => [],
        };
    }

    public function requiredDocuments(): array
    {
        return $this->db()->table('documents')
            ->whereNull('deleted_at')
            ->orderByDesc('is_required')
            ->orderBy('name')
            ->get(['name', 'is_required', 'status'])
            ->map(fn ($d) => [
                'name'     => $d->name,
                'required' => (bool) $d->is_required,
                'active'   => (bool) $d->status,
            ])
            ->all();
    }
}
