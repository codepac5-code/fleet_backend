<?php

namespace App\Http\Services\Panel\Admin\Offices\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OfficeAnalytics
{
    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    private function db()
    {
        return DB::connection($this->connection());
    }

    public function counts(Office $office): array
    {
        return [
            'drivers'  => (int) $this->db()->table('drivers')->where('officeId', $office->id)->whereNull('deleted_at')->count(),
            'vehicles' => (int) $this->db()->table('vehicles')->where('officeId', $office->id)->whereNull('deleted_at')->count(),
            'services' => (int) $this->db()->table('bookings')->where('officeId', $office->id)->whereNull('deleted_at')->whereNotNull('subServiceId')->distinct()->count('subServiceId'),
        ];
    }

    public function overview(Office $office): array
    {
        $done = OrderStatus::$Completed;
        $today = Carbon::today();
        $week = Carbon::now()->startOfWeek();
        $month = Carbon::now()->startOfMonth();

        $row = $this->db()->table('bookings')
            ->where('officeId', $office->id)
            ->whereNull('deleted_at')
            ->selectRaw(
                'COUNT(*) AS all_trips,
                 SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS total_trips,
                 SUM(CASE WHEN status = ? THEN totalAmount ELSE 0 END) AS total_revenue,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_today,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN totalAmount ELSE 0 END) AS rev_today,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_week,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN totalAmount ELSE 0 END) AS rev_week,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_month,
                 SUM(CASE WHEN status = ? AND created_at >= ? THEN totalAmount ELSE 0 END) AS rev_month',
                [$done, $done, $done, $today, $done, $today, $done, $week, $done, $week, $done, $month, $done, $month]
            )->first();

        return [
            'allTrips'     => (int) ($row->all_trips ?? 0),
            'totalTrips'   => (int) ($row->total_trips ?? 0),
            'totalRevenue' => (float) ($row->total_revenue ?? 0),
            'periods'      => [
                'today' => ['trips' => (int) ($row->trips_today ?? 0), 'revenue' => (float) ($row->rev_today ?? 0)],
                'week'  => ['trips' => (int) ($row->trips_week ?? 0), 'revenue' => (float) ($row->rev_week ?? 0)],
                'month' => ['trips' => (int) ($row->trips_month ?? 0), 'revenue' => (float) ($row->rev_month ?? 0)],
            ],
        ];
    }

    public function statsForDate(Office $office, Carbon $date): array
    {
        $row = $this->db()->table('bookings')
            ->where('officeId', $office->id)
            ->whereNull('deleted_at')
            ->where('status', OrderStatus::$Completed)
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->selectRaw('COUNT(*) AS trips, COALESCE(SUM(totalAmount), 0) AS revenue')
            ->first();

        return ['trips' => (int) $row->trips, 'revenue' => (float) $row->revenue];
    }

    public function wallet(Office $office): array
    {
        return [
            'balance'     => (float) $office->walletBalance,
            'fleetDues'   => (float) $office->fleetDues,
            'driversDues' => (float) $office->driversDues,
        ];
    }

    public function driversFeed(Office $office, int $perPage = 8): LengthAwarePaginator
    {
        $done = OrderStatus::$Completed;

        return $this->db()->table('drivers')
            ->where('drivers.officeId', $office->id)
            ->whereNull('drivers.deleted_at')
            ->leftJoin('bookings', function ($join) use ($office, $done) {
                $join->on('bookings.driverId', '=', 'drivers.id')
                    ->where('bookings.officeId', '=', $office->id)
                    ->where('bookings.status', '=', $done)
                    ->whereNull('bookings.deleted_at');
            })
            ->groupBy('drivers.id', 'drivers.firstName', 'drivers.lastName', 'drivers.rating')
            ->selectRaw("drivers.id,
                TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) AS name,
                drivers.rating,
                COUNT(bookings.id) AS trips, COALESCE(SUM(bookings.totalAmount), 0) AS revenue")
            ->orderByDesc('trips')
            ->paginate($perPage);
    }

    public function vehiclesFeed(Office $office, int $perPage = 8): LengthAwarePaginator
    {
        return $this->db()->table('vehicles')
            ->where('vehicles.officeId', $office->id)
            ->whereNull('vehicles.deleted_at')
            ->leftJoin('drivers', 'drivers.id', '=', 'vehicles.driverId')
            ->selectRaw("vehicles.id, vehicles.vehicleBrand, vehicles.model, vehicles.plate, vehicles.seatsCount,
                TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) AS driver")
            ->orderByDesc('vehicles.id')
            ->paginate($perPage);
    }

    public function servicesFeed(Office $office, int $perPage = 8): LengthAwarePaginator
    {
        $isAr = app()->getLocale() === 'ar';
        $name = $isAr ? "COALESCE(sub_services.name, sub_services.name_en)" : "COALESCE(sub_services.name_en, sub_services.name)";

        return $this->db()->table('bookings')
            ->where('bookings.officeId', $office->id)
            ->whereNull('bookings.deleted_at')
            ->whereNotNull('bookings.subServiceId')
            ->join('sub_services', 'sub_services.id', '=', 'bookings.subServiceId')
            ->groupBy('bookings.subServiceId', 'sub_services.name', 'sub_services.name_en')
            ->selectRaw("bookings.subServiceId AS id, {$name} AS name,
                COUNT(*) AS trips, COALESCE(SUM(bookings.totalAmount), 0) AS revenue")
            ->orderByDesc('trips')
            ->paginate($perPage);
    }
}
