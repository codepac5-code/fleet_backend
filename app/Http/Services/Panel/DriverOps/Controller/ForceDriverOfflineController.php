<?php

namespace App\Http\Services\Panel\DriverOps\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ForceDriverOfflineController extends Controller
{
    public function __invoke(int $driver, Request $request, EntityScope $scope, DispatchService $dispatch, AuditLogService $audit): RedirectResponse
    {
        $model = Driver::on(TenantConnection::current())->find($driver);

        // Scope guard: an office may only force its OWN drivers offline; an admin
        // any driver on the active country shard. Never across shards/offices.
        if ($model === null || (! $scope->isAdmin() && (int) $model->officeId !== (int) $scope->officeId())) {
            return back()->with('error', textByLanguage('غير مسموح', 'Not allowed'));
        }

        $dispatch->heartbeat((int) $model->id, $model->officeId !== null ? (int) $model->officeId : null, PresenceStatus::OFFLINE, null, null);

        $audit->record(
            'driver.force_offline',
            $scope->isAdmin() ? 'admin' : 'office',
            $scope->isAdmin() ? null : $scope->officeId(),
            'driver',
            (int) $model->id,
            [],
            $request->ip()
        );

        return back()->with('status', textByLanguage('تم تحويل السائق إلى غير متصل', 'Driver forced offline'));
    }
}
