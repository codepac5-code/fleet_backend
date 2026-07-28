<?php

namespace App\Http\Services\Panel\DriverOps\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\DriverPresence;
use Illuminate\View\View;

class DriverPresencePageController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        $rows = $this->rows($scope);

        return view('panel.driver-presence.index', [
            'entity' => $scope->guard(),
            'rows' => $rows,
            'isAdmin' => $scope->isAdmin(),
            'counts' => [
                'online' => $rows->where('status', PresenceStatus::ONLINE)->count(),
                'busy' => $rows->where('status', PresenceStatus::BUSY)->count(),
                'offline' => $rows->filter(fn ($r) => $r['status'] !== PresenceStatus::ONLINE && $r['status'] !== PresenceStatus::BUSY)->count(),
            ],
        ]);
    }

    private function rows(EntityScope $scope)
    {
        $conn = TenantConnection::current();

        $query = DriverPresence::on($conn)->orderByDesc('heartbeat_at');
        $scope->scopeByOffice($query, 'office_id');

        $presences = $query->limit(300)->get();

        $drivers = Driver::on($conn)
            ->whereIn('id', $presences->pluck('driver_id')->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber', 'officeId'])
            ->keyBy('id');

        return $presences->map(function (DriverPresence $p) use ($drivers) {
            $driver = $drivers->get($p->driver_id);

            return [
                'driver_id' => (int) $p->driver_id,
                'name' => $driver ? trim(($driver->firstName ?? '') . ' ' . ($driver->lastName ?? '')) : null,
                'phone' => $driver->phoneNumber ?? null,
                'office_id' => $p->office_id !== null ? (int) $p->office_id : null,
                'status' => (string) $p->status,
                'busy_reason' => $p->busy_reason,
                'heartbeat_at' => $p->heartbeat_at,
            ];
        })->values();
    }
}
