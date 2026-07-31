<?php

namespace App\Http\Services\Panel\Commissions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateOfficeCommissionController extends Controller
{
    public function __construct(private CommissionResolver $commission, private AuditLogService $audit)
    {
    }

    public function __invoke(Request $request, EntityScope $scope): RedirectResponse
    {
        $officeId = (int) $scope->officeId();

        if ($officeId <= 0) {
            throw new NotFoundHttpException();
        }

        $office = Office::on(TenantConnection::current())->findOrFail($officeId);

        // The office may take at most what the platform left behind — anything
        // more would settle a ride by taking money the driver never earned.
        $ceiling = round(100.0 - $this->commission->forOffice($officeId)['fleet_rate'], 2);

        $data = $request->validate([
            'driver_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:' . $ceiling],
        ], [
            'driver_commission_rate.max' => textByLanguage(
                'أقصى عمولة يمكنك أخذها من السائق هي ' . $ceiling . '% — الباقي حصّة فليت.',
                'The most you can take from a driver is ' . $ceiling . '% — the rest is FleetOS’s cut.'
            ),
        ]);

        $previous = $office->driver_commission_rate;
        $rate = $data['driver_commission_rate'] ?? null;

        $office->driver_commission_rate = $rate === null ? null : round((float) $rate, 2);
        $office->save();

        $this->audit->record(
            $rate === null ? 'office.driver_commission_cleared' : 'office.driver_commission_set',
            $scope->guard(),
            $scope->user()?->id,
            'office',
            $officeId,
            ['from' => $previous, 'to' => $office->driver_commission_rate],
            $request->ip()
        );

        return redirect()
            ->route('panel.' . $scope->guard() . '.commission.index')
            ->with('status', textByLanguage('تم تحديث عمولة السائقين', 'Driver commission updated'));
    }
}
