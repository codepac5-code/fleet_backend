<?php

namespace App\Http\Services\Panel\Commissions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Driver;
use App\Models\Office;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * How a fare is divided, from the office's side.
 *
 * The platform takes its cut off the top — the office cannot change that, only
 * see it. What is left belongs to the office and the driver together, and this
 * is where the office says how much of it is its own. A driver with a
 * negotiated rate keeps that rate; everyone else follows the number set here.
 */
class OfficeCommissionPageController extends Controller
{
    public function __construct(private CommissionResolver $commission)
    {
    }

    public function __invoke(EntityScope $scope): View
    {
        $officeId = (int) $scope->officeId();

        if ($officeId <= 0) {
            throw new NotFoundHttpException();
        }

        $connection = TenantConnection::current();
        $office = Office::on($connection)->findOrFail($officeId);
        $rates = $this->commission->forOffice($officeId);

        $drivers = Driver::on($connection)
            ->where('officeId', $officeId)
            ->orderBy('id')
            ->get(['id', 'firstName', 'lastName', 'phoneNumber', 'commission_rate_override'])
            ->map(fn ($d) => [
                'id' => (int) $d->id,
                'name' => trim(($d->firstName ?? '') . ' ' . ($d->lastName ?? '')) ?: ('#' . $d->id),
                'phone' => $d->phoneNumber,
                'override' => $d->commission_rate_override !== null ? (float) $d->commission_rate_override : null,
                'effective' => $d->commission_rate_override !== null ? (float) $d->commission_rate_override : $rates['office_rate'],
            ])
            ->all();

        return view('panel.commissions.office', [
            'entity' => $scope->guard(),
            'currency' => ShardManager::currency(),
            'fleetRate' => $rates['fleet_rate'],
            'officeRate' => $rates['office_rate'],
            // What the office may ever take: everything the platform did not.
            'ceiling' => round(100.0 - $rates['fleet_rate'], 2),
            'configured' => $office->driver_commission_rate !== null ? (float) $office->driver_commission_rate : null,
            'drivers' => $drivers,
        ]);
    }
}
