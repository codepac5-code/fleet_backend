<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sets (or clears) a driver's negotiated commission rate. Stored as a percentage
 * — the same unit every other rate in the settlement path uses. Clearing returns
 * the driver to the office's plan rate.
 */
class UpdateDriverCommissionController extends Controller
{
    public function __invoke(Request $request, int $driver, EntityScope $scope, DriverRepository $drivers, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $model = $drivers->findOrFail($driver);

        $percent = $data['commission_percent'] ?? null;
        $percent = $percent === '' ? null : $percent;
        $previous = $model->commission_rate_override;

        $model->commission_rate_override = $percent === null ? null : round((float) $percent, 2);
        $model->save();

        $audit->record(
            $percent === null ? 'driver.commission_override_cleared' : 'driver.commission_override_set',
            $scope->guard(),
            $scope->user()?->id,
            'driver',
            (int) $model->id,
            ['from' => $previous, 'to' => $model->commission_rate_override],
            $request->ip()
        );

        return back()->with('status', $percent === null
            ? textByLanguage('عاد السائق إلى عمولة المكتب.', 'The driver is back on the office rate.')
            : textByLanguage('تم تحديث عمولة السائق.', 'Driver commission updated.'));
    }
}
