<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SuspendDriverController extends Controller
{
    public function __invoke(int $driver, Request $request, EntityScope $scope, DriverRepository $drivers, AuditLogService $audit): RedirectResponse
    {
        // DriverRepository scopes by the acting office, so an office can only
        // suspend its OWN drivers and only on the active country shard.
        $model = $drivers->findOrFail($driver);

        $suspending = (bool) $model->isActive;

        $data = $request->validate([
            'reason' => [$suspending ? 'required' : 'nullable', 'string', 'max:500'],
        ]);

        $model->isActive = $suspending ? 0 : 1;
        $model->save();

        $audit->record(
            $suspending ? 'driver.suspended' : 'driver.reinstated',
            $scope->isAdmin() ? 'admin' : 'office',
            $scope->isAdmin() ? null : $scope->officeId(),
            'driver',
            (int) $model->id,
            array_filter(['reason' => $data['reason'] ?? null]),
            $request->ip()
        );

        return back()->with('status', $suspending
            ? textByLanguage('تم إيقاف السائق', 'Driver suspended')
            : textByLanguage('تمت إعادة تفعيل السائق', 'Driver reinstated'));
    }
}
