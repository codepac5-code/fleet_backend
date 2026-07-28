<?php

namespace App\Http\Services\Panel\Services\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Service;
use App\Models\SubService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ServiceAnalytics
{
    private ?array $subIdsCache = null;

    private function connection(): ?string
    {
        return TenantConnection::current();
    }

    public function subServiceIds(Service $service): array
    {
        return $this->subIdsCache ??= SubService::on($this->connection())
            ->where('serviceId', $service->id)
            ->pluck('id')
            ->all();
    }

    private function bookings(array $subIds): Builder
    {
        return DB::connection($this->connection())
            ->table('bookings')
            ->whereIn('bookings.subServiceId', $subIds)
            ->whereNull('bookings.deleted_at');
    }

    public function overview(Service $service): array
    {
        $subIds = $this->subServiceIds($service);

        if (empty($subIds)) {
            return [
                'offices'      => 0,
                'drivers'      => 0,
                'totalTrips'   => 0,
                'totalRevenue' => 0.0,
                'periods'      => $this->emptyPeriods(),
            ];
        }

        $done = OrderStatus::$Completed;
        $today = Carbon::today();
        $week = Carbon::now()->startOfWeek();
        $month = Carbon::now()->startOfMonth();

        $offices = (int) $this->bookings($subIds)->whereNotNull('bookings.officeId')->distinct()->count('bookings.officeId');
        $drivers = (int) $this->bookings($subIds)->whereNotNull('bookings.driverId')->distinct()->count('bookings.driverId');

        $row = $this->bookings($subIds)->selectRaw(
            'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) AS total_trips,
             SUM(CASE WHEN status = ? THEN totalAmount ELSE 0 END) AS total_revenue,
             SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_today,
             SUM(CASE WHEN status = ? AND created_at >= ? THEN totalAmount ELSE 0 END) AS rev_today,
             SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_week,
             SUM(CASE WHEN status = ? AND created_at >= ? THEN totalAmount ELSE 0 END) AS rev_week,
             SUM(CASE WHEN status = ? AND created_at >= ? THEN 1 ELSE 0 END) AS trips_month,
             SUM(CASE WHEN status = ? AND created_at >= ? THEN totalAmount ELSE 0 END) AS rev_month',
            [
                $done, $done,
                $done, $today, $done, $today,
                $done, $week, $done, $week,
                $done, $month, $done, $month,
            ]
        )->first();

        return [
            'offices'      => $offices,
            'drivers'      => $drivers,
            'totalTrips'   => (int) ($row->total_trips ?? 0),
            'totalRevenue' => (float) ($row->total_revenue ?? 0),
            'periods'      => [
                'today' => ['trips' => (int) ($row->trips_today ?? 0), 'revenue' => (float) ($row->rev_today ?? 0)],
                'week'  => ['trips' => (int) ($row->trips_week ?? 0), 'revenue' => (float) ($row->rev_week ?? 0)],
                'month' => ['trips' => (int) ($row->trips_month ?? 0), 'revenue' => (float) ($row->rev_month ?? 0)],
            ],
        ];
    }

    public function statsForDate(Service $service, Carbon $date): array
    {
        $subIds = $this->subServiceIds($service);

        if (empty($subIds)) {
            return ['trips' => 0, 'revenue' => 0.0];
        }

        $row = $this->bookings($subIds)
            ->where('status', OrderStatus::$Completed)
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->selectRaw('COUNT(*) AS trips, COALESCE(SUM(totalAmount), 0) AS revenue')
            ->first();

        return ['trips' => (int) $row->trips, 'revenue' => (float) $row->revenue];
    }

    public function driversFeed(Service $service, int $perPage = 10): LengthAwarePaginator
    {
        $subIds = $this->subServiceIds($service);

        return $this->bookings($subIds ?: [0])
            ->whereNotNull('bookings.driverId')
            ->join('drivers', 'drivers.id', '=', 'bookings.driverId')
            ->whereNull('drivers.deleted_at')
            ->groupBy('bookings.driverId', 'drivers.firstName', 'drivers.lastName', 'drivers.phoneNumber', 'drivers.dialCode', 'drivers.rating')
            ->selectRaw("bookings.driverId AS id,
                TRIM(CONCAT(COALESCE(drivers.firstName, ''), ' ', COALESCE(drivers.lastName, ''))) AS name,
                drivers.phoneNumber, drivers.dialCode, drivers.rating,
                COUNT(*) AS trips, COALESCE(SUM(bookings.totalAmount), 0) AS revenue")
            ->orderByDesc('trips')
            ->paginate($perPage);
    }

    public function officesFeed(Service $service, int $perPage = 10): LengthAwarePaginator
    {
        $subIds = $this->subServiceIds($service);

        return $this->bookings($subIds ?: [0])
            ->whereNotNull('bookings.officeId')
            ->join('offices', 'offices.id', '=', 'bookings.officeId')
            ->groupBy('bookings.officeId', 'offices.officeName', 'offices.city')
            ->selectRaw("bookings.officeId AS id, offices.officeName AS name, offices.city,
                COUNT(*) AS trips, COALESCE(SUM(bookings.totalAmount), 0) AS revenue,
                COUNT(DISTINCT bookings.driverId) AS drivers")
            ->orderByDesc('trips')
            ->paginate($perPage);
    }

    private function emptyPeriods(): array
    {
        $zero = ['trips' => 0, 'revenue' => 0.0];

        return ['today' => $zero, 'week' => $zero, 'month' => $zero];
    }
}
