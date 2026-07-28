<?php

namespace App\Http\Services\Panel\Home\Logic;

use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\BalanceStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletRedisModel;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Booking;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\FleetOffice;
use App\Models\Office;
use App\Models\Service;
use App\Models\User;
use App\Models\WalletBalance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardData
{
    public function __construct(private EntityScope $scope) {}

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    private function scopedBookings(): Builder
    {
        $query = Booking::on($this->connection());

        return $this->scope->isAdmin()
            ? $query
            : $query->where('officeId', $this->scope->officeId());
    }

    public function counters(): array
    {
        $conn = $this->connection();

        if ($this->scope->isAdmin()) {
            return [
                ['id' => 'offices',  'label' => __('messages.offices'),  'value' => Office::on($conn)->count(),  'icon' => 'bi-building'],
                ['id' => 'users',    'label' => __('messages.users'),    'value' => User::query()->count(),    'icon' => 'bi-people'],
                ['id' => 'drivers',  'label' => __('messages.drivers'),  'value' => Driver::on($conn)->count(),  'icon' => 'bi-taxi-front'],
                ['id' => 'services', 'label' => __('messages.services'), 'value' => Service::on($conn)->count(), 'icon' => 'bi-grid-1x2'],
            ];
        }

        $officeId = $this->scope->officeId();

        return [
            ['id' => 'drivers',  'label' => __('messages.drivers'),  'value' => Driver::on($conn)->where('officeId', $officeId)->count(), 'icon' => 'bi-taxi-front'],
            ['id' => 'orders',   'label' => __('messages.orders'),   'value' => $this->scopedBookings()->count(),                        'icon' => 'bi-card-checklist'],
            ['id' => 'services', 'label' => __('messages.services'), 'value' => Service::on($conn)->count(),                              'icon' => 'bi-grid-1x2'],
        ];
    }

    public function walletSummary(): array
    {
        $conn    = $this->connection();

        try {
            $pending = FleetWalletRedisModel::getBalanceValueByStatus(BalanceStatus::$Pending) ?? 0;
        } catch (\Throwable $e) {
            $pending = 0;
        }

        if ($this->scope->isAdmin()) {
            return [
                'balance'    => FleetOffice::on($conn)->value('walletBalance') ?? 0,
                'pending'    => $pending,
                'driverDues' => Driver::on($conn)->sum('fleetDues'),
                'officeDues' => Office::on($conn)->sum('fleetDues'),
            ];
        }

        $officeId = $this->scope->officeId();
        $office   = Office::on($conn)->select(['walletBalance', 'fleetDues'])->find($officeId);

        return [
            'balance'    => $office?->walletBalance ?? 0,
            'pending'    => $pending,
            'driverDues' => Driver::on($conn)->where('officeId', $officeId)->sum('officeDues'),
            'officeDues' => $office?->fleetDues ?? 0,
        ];
    }

    private function walletOwner(): ?array
    {
        $conn = $this->connection();

        if ($this->scope->isAdmin()) {
            $fleet = FleetOffice::on($conn)->first(['id', 'walletBalance']);

            return $fleet
                ? ['type' => FleetOffice::class, 'id' => $fleet->id, 'legacy' => (float) $fleet->walletBalance]
                : null;
        }

        $office = Office::on($conn)->find($this->scope->officeId(), ['id', 'walletBalance']);

        return $office
            ? ['type' => Office::class, 'id' => $office->id, 'legacy' => (float) $office->walletBalance]
            : null;
    }

    public function currencyBalances(): array
    {
        $owner = $this->walletOwner();

        if (! $owner) {
            return [];
        }

        $currencies = Currency::where('is_active', true)->get()->keyBy('code');

        $rows = WalletBalance::on($this->connection())
            ->where('owner_type', $owner['type'])
            ->where('owner_id', $owner['id'])
            ->get();

        if ($rows->isNotEmpty()) {
            return $rows->map(function ($row) use ($currencies) {
                $currency = $currencies->get($row->currency_code);

                return [
                    'code'    => $row->currency_code,
                    'symbol'  => $currency->symbol ?? $row->currency_code,
                    'balance' => (float) $row->balance,
                ];
            })->all();
        }

        $default = $currencies->firstWhere('is_default', true) ?? $currencies->first();

        return [[
            'code'    => $default->code ?? null,
            'symbol'  => $default->symbol ?? getPriceSymbol(),
            'balance' => $owner['legacy'],
        ]];
    }

    public function monthlyRevenue(): array
    {
        $rows = $this->scopedBookings()
            ->selectRaw('MONTH(created_at) as m, SUM(totalAmount) as t')
            ->groupBy('m')
            ->pluck('t', 'm');

        $data = array_fill(1, 12, 0);
        foreach ($rows as $month => $total) {
            $data[$month] = round((float) $total, 2);
        }

        return array_values($data);
    }

    public function liveKpis(): array
    {
        $isAdmin  = $this->scope->isAdmin();
        $officeId = $this->scope->officeId();

        if ($isAdmin) {
            $ongoing   = RedisManagerData::get_system_daily_ongoing_rides();
            $pending   = RedisManagerData::get_system_daily_pending_rides();
            $completed = RedisManagerData::get_system_daily_completed_rides();
        } else {
            $ongoing   = RedisManagerData::get_office_daily_ongoing_rides($officeId);
            $pending   = RedisManagerData::get_office_daily_pending_rides($officeId);
            $completed = RedisManagerData::get_office_daily_completed_rides($officeId);
        }

        $today          = Carbon::today();
        $trips          = (clone $this->scopedBookings())->where('created_at', '>=', $today);
        $todayTotal     = (clone $trips)->count();
        $todayCancelled = (clone $trips)->where('status', OrderStatus::$Cancelled)->count();
        $revenue        = (clone $trips)->sum('totalAmount');
        $cancelRate     = $todayTotal > 0 ? (int) round($todayCancelled / $todayTotal * 100) : 0;

        return [
            ['id' => 'kpi-online',     'tone' => 'green',  'icon' => 'bi-broadcast',       'label' => textByLanguage('سائقون متّصلون', 'Online drivers'), 'value' => number_format($this->onlineDriversCount()), 'live' => true],
            ['id' => 'ongoing-ride',   'tone' => 'amber',  'icon' => 'bi-truck',           'label' => textByLanguage('رحلات جاريّة', 'Active trips'),     'value' => number_format((int) $ongoing),   'live' => true],
            ['id' => 'pending-ride',   'tone' => 'indigo', 'icon' => 'bi-hourglass-split', 'label' => textByLanguage('بانتظار سائق', 'Waiting'),          'value' => number_format((int) $pending),   'live' => true],
            ['id' => 'completed-ride', 'tone' => 'teal',   'icon' => 'bi-check2-circle',   'label' => textByLanguage('مكتمل اليوم', 'Completed today'),   'value' => number_format((int) $completed), 'live' => true],
            ['id' => 'kpi-revenue',    'tone' => 'gold',   'icon' => 'bi-cash-coin',       'label' => textByLanguage('إيراد اليوم', 'Revenue today'),     'value' => getPriceFormat($revenue),        'live' => false],
            ['id' => 'kpi-cancel',     'tone' => 'red',    'icon' => 'bi-x-octagon',       'label' => textByLanguage('معدّل الإلغاء', 'Cancel rate'),     'value' => $cancelRate . '%',               'live' => false],
        ];
    }

    public function liveValues(): array
    {
        $values = [];

        foreach ($this->liveKpis() as $k) {
            $values[$k['id']] = $k['value'];
        }

        foreach ($this->counters() as $c) {
            $values[$c['id']] = number_format((int) $c['value']);
        }

        return $values;
    }

    private function onlineDriversCount(): int
    {
        $list = RedisManagerData::getOnlineDriversMapLocations();

        if (! is_array($list)) {
            return 0;
        }

        if ($this->scope->isAdmin()) {
            return count($list);
        }

        $ids = Driver::on($this->connection())
            ->where('officeId', $this->scope->officeId())
            ->pluck('id')
            ->map(fn ($i) => (string) $i)
            ->all();

        return count(array_filter($list, fn ($d) => in_array((string) ($d['driver_id'] ?? ''), $ids, true)));
    }

    public function heroStats(): array
    {
        $conn  = $this->connection();
        $today = Carbon::today();
        $trips = (clone $this->scopedBookings())->where('created_at', '>=', $today);

        $driverQuery = Driver::on($conn);
        if (! $this->scope->isAdmin()) {
            $driverQuery->where('officeId', $this->scope->officeId());
        }

        $newDrivers = (clone $driverQuery)->where('created_at', '>=', $today)->count();

        $stats = [
            ['label' => textByLanguage('رحلات اليوم', 'Trips today'),     'icon' => 'bi-card-checklist', 'value' => number_format((clone $trips)->count())],
            ['label' => textByLanguage('سائقون جدد', 'New drivers'),      'icon' => 'bi-person-plus',     'value' => number_format($newDrivers)],
            ['label' => textByLanguage('إيرادات اليوم', 'Revenue today'), 'icon' => 'bi-cash-stack',      'value' => getPriceFormat((clone $trips)->sum('totalAmount'))],
        ];

        if ($this->scope->isAdmin()) {
            array_splice($stats, 2, 0, [[
                'label' => textByLanguage('مشتركون جدد', 'New users'),
                'icon'  => 'bi-people',
                'value' => number_format(User::query()->where('created_at', '>=', $today)->count()),
            ]]);
        }

        return $stats;
    }

    public function periodStats(): array
    {
        return [
            'today' => $this->statsForRange(Carbon::today()),
            'week'  => $this->statsForRange(Carbon::now()->startOfWeek()),
            'month' => $this->statsForRange(Carbon::now()->startOfMonth()),
        ];
    }

    public function rangeStats(Carbon $from, Carbon $to): array
    {
        return $this->statsForRange($from->copy()->startOfDay(), $to->copy()->endOfDay());
    }

    private function inRange(Builder $query, Carbon $from, ?Carbon $to): Builder
    {
        $query->where('created_at', '>=', $from);

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }

    private function statsForRange(Carbon $from, ?Carbon $to = null): array
    {
        $conn  = $this->connection();
        $trips = $this->inRange((clone $this->scopedBookings()), $from, $to);

        $tripsCount = (clone $trips)->count();
        $revenue    = (clone $trips)->sum('totalAmount');

        if ($this->scope->isAdmin()) {
            return [
                ['label' => textByLanguage('الرحلات', 'Trips'),          'icon' => 'bi-card-checklist', 'value' => $tripsCount, 'money' => false],
                ['label' => textByLanguage('مشتركون جدد', 'New users'),  'icon' => 'bi-person-plus',    'value' => $this->inRange(User::query(), $from, $to)->count(), 'money' => false],
                ['label' => textByLanguage('مكاتب جديدة', 'New offices'), 'icon' => 'bi-buildings',      'value' => $this->inRange(Office::on($conn), $from, $to)->count(), 'money' => false],
                ['label' => textByLanguage('الإيرادات', 'Revenue'),      'icon' => 'bi-cash-stack',     'value' => $revenue, 'money' => true],
            ];
        }

        $officeId = $this->scope->officeId();

        return [
            ['label' => textByLanguage('الرحلات', 'Trips'),           'icon' => 'bi-card-checklist', 'value' => $tripsCount, 'money' => false],
            ['label' => textByLanguage('سائقون جدد', 'New drivers'),  'icon' => 'bi-person-plus',    'value' => $this->inRange(Driver::on($conn)->where('officeId', $officeId), $from, $to)->count(), 'money' => false],
            ['label' => textByLanguage('الإيرادات', 'Revenue'),       'icon' => 'bi-cash-stack',     'value' => $revenue, 'money' => true],
        ];
    }

    public function rides(): array
    {
        return [
            'completed' => RedisManagerData::get_system_daily_completed_rides(),
            'ongoing'   => RedisManagerData::get_system_daily_ongoing_rides(),
            'pending'   => RedisManagerData::get_system_daily_pending_rides(),
        ];
    }

    public function periodStatus(): array
    {
        return [
            'today' => $this->statusBreakdown(Carbon::today()),
            'week'  => $this->statusBreakdown(Carbon::now()->startOfWeek()),
            'month' => $this->statusBreakdown(Carbon::now()->startOfMonth()),
        ];
    }

    public function statusBreakdown(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = $this->scopedBookings();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        $counts = $query
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $palette = [
            OrderStatus::$Completed => ['label' => textByLanguage('مكتملة', 'Completed'),  'color' => '#16a34a'],
            OrderStatus::$OnGoing   => ['label' => textByLanguage('جارية', 'Ongoing'),     'color' => '#312873'],
            OrderStatus::$InProgress=> ['label' => textByLanguage('قيد التنفيذ', 'In progress'), 'color' => '#3b82f6'],
            OrderStatus::$Pending   => ['label' => textByLanguage('معلّقة', 'Pending'),     'color' => '#F8A609'],
            OrderStatus::$Cancelled => ['label' => textByLanguage('ملغاة', 'Cancelled'),    'color' => '#dc2626'],
        ];

        $parts = [];
        foreach ($palette as $status => $meta) {
            $value = (int) ($counts[$status] ?? 0);
            if ($value > 0) {
                $parts[] = ['label' => $meta['label'], 'value' => $value, 'color' => $meta['color']];
            }
        }

        return $parts;
    }

    public function recentOrders(int $limit = 8): array
    {
        $conn = $this->connection();

        $query = Booking::on($conn)
            ->leftJoin('users', 'bookings.userId', '=', 'users.id')
            ->leftJoin('drivers', 'bookings.driverId', '=', 'drivers.id')
            ->select([
                'bookings.id',
                'bookings.status',
                'bookings.totalAmount',
                'bookings.created_at',
                DB::raw("TRIM(CONCAT(COALESCE(users.firstName, ''), ' ', COALESCE(users.lastName, ''))) as customer"),
                DB::raw("TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) as driver"),
            ])
            ->latest('bookings.created_at')
            ->limit($limit);

        if (! $this->scope->isAdmin()) {
            $query->where('bookings.officeId', $this->scope->officeId());
        }

        return $query->get()->map(fn ($r) => [
            'id'       => $r->id,
            'status'   => $r->status,
            'amount'   => (float) $r->totalAmount,
            'customer' => trim((string) $r->customer) ?: '—',
            'driver'   => trim((string) $r->driver) ?: '—',
            'when'     => $r->created_at ? Carbon::parse($r->created_at)->diffForHumans() : '',
        ])->all();
    }

    public function rankings(): array
    {
        $out = [
            'rating'   => ['label' => textByLanguage('الأعلى تقييماً', 'Top rated'),    'icon' => 'bi-star-fill',       'rows' => $this->topDriversByRating(7)],
            'distance' => ['label' => textByLanguage('الأكثر مسافة', 'Most distance'),  'icon' => 'bi-signpost-split',  'rows' => $this->topDriversByDistance(7)],
        ];

        if ($this->scope->isAdmin()) {
            $out['offices'] = ['label' => textByLanguage('أفضل المكاتب', 'Top offices'), 'icon' => 'bi-buildings', 'rows' => $this->topOffices(7)];
        }

        return $out;
    }

    private function topDriversByRating(int $limit = 5): array
    {
        $query = Driver::on($this->connection())->where('rating', '>', 0);

        if (! $this->scope->isAdmin()) {
            $query->where('officeId', $this->scope->officeId());
        }

        $rows = $query->orderByDesc('rating')->limit($limit)->get(['id', 'firstName', 'lastName', 'photo', 'rating']);

        return $rows->map(fn ($d) => [
            'name'   => trim(($d->firstName ?? '') . ' ' . ($d->lastName ?? '')) ?: ('#' . $d->id),
            'photo'  => $d->photo,
            'metric' => number_format((float) $d->rating, 1),
            'unit'   => '★',
            'pct'    => min(100, (float) $d->rating / 5 * 100),
        ])->all();
    }

    private function topDriversByDistance(int $limit = 5): array
    {
        $query = Booking::on($this->connection())
            ->leftJoin('drivers', 'bookings.driverId', '=', 'drivers.id')
            ->whereNotNull('bookings.driverId');

        if (! $this->scope->isAdmin()) {
            $query->where('bookings.officeId', $this->scope->officeId());
        }

        $rows = $query
            ->selectRaw("TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) as name, drivers.photo as photo, SUM(bookings.distance) as dist")
            ->groupBy('bookings.driverId', 'drivers.firstName', 'drivers.lastName', 'drivers.photo')
            ->orderByDesc('dist')
            ->limit($limit)
            ->get();

        $max = (float) ($rows->max('dist') ?: 1);

        return $rows->map(fn ($r) => [
            'name'   => trim((string) $r->name) ?: '—',
            'photo'  => $r->photo,
            'metric' => number_format((float) $r->dist, 0),
            'unit'   => textByLanguage('كم', 'km'),
            'pct'    => $max > 0 ? (float) $r->dist / $max * 100 : 0,
        ])->all();
    }

    private function topOffices(int $limit = 5): array
    {
        $from = Carbon::now()->startOfMonth();

        $rows = Booking::on($this->connection())
            ->leftJoin('offices', 'bookings.officeId', '=', 'offices.id')
            ->where('bookings.created_at', '>=', $from)
            ->selectRaw('offices.officeName as name, COUNT(*) as trips, SUM(bookings.totalAmount) as revenue')
            ->groupBy('bookings.officeId', 'offices.officeName')
            ->orderByDesc('trips')
            ->limit($limit)
            ->get();

        $max = (float) ($rows->max('trips') ?: 1);

        return $rows->map(fn ($r) => [
            'name'   => trim((string) $r->name) ?: '—',
            'metric' => number_format((int) $r->trips),
            'unit'   => textByLanguage('رحلة', 'trips'),
            'sub'    => getPriceFormat((float) $r->revenue),
            'pct'    => $max > 0 ? (int) $r->trips / $max * 100 : 0,
        ])->all();
    }

    public function leaderboard(int $limit = 5): array
    {
        $conn = $this->connection();
        $from = Carbon::now()->startOfMonth();

        if ($this->scope->isAdmin()) {
            $rows = Booking::on($conn)
                ->leftJoin('offices', 'bookings.officeId', '=', 'offices.id')
                ->where('bookings.created_at', '>=', $from)
                ->selectRaw('offices.officeName as name, COUNT(*) as trips, SUM(bookings.totalAmount) as revenue')
                ->groupBy('bookings.officeId', 'offices.officeName')
                ->orderByDesc('trips')
                ->limit($limit)
                ->get();
        } else {
            $rows = Booking::on($conn)
                ->leftJoin('drivers', 'bookings.driverId', '=', 'drivers.id')
                ->where('bookings.officeId', $this->scope->officeId())
                ->where('bookings.created_at', '>=', $from)
                ->selectRaw("TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) as name, COUNT(*) as trips, SUM(bookings.totalAmount) as revenue")
                ->groupBy('bookings.driverId', 'drivers.firstName', 'drivers.lastName')
                ->orderByDesc('trips')
                ->limit($limit)
                ->get();
        }

        return $rows->map(fn ($r) => [
            'name'    => trim((string) $r->name) ?: '—',
            'trips'   => (int) $r->trips,
            'revenue' => (float) $r->revenue,
        ])->all();
    }
}
