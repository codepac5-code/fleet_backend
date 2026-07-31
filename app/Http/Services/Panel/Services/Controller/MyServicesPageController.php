<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Services\Logic\PricingRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * What THIS office sells, edited by the office itself.
 *
 * The pair (`sub_services`, `office_sub_service_prices`) is what the rider
 * marketplace searches, yet the only screen that could edit it lived in the
 * admin panel — an office could not put itself on a service, or take itself
 * off one, without asking the platform to do it.
 */
class MyServicesPageController extends Controller
{
    public function __invoke(EntityScope $scope, PricingRepository $pricing): View
    {
        $officeId = (int) $scope->officeId();

        if ($officeId <= 0) {
            throw new NotFoundHttpException();
        }

        $office = Office::on(TenantConnection::current())->find($officeId);
        $serviceIds = $office !== null ? $office->serviceIds() : [];

        return view('panel.services.my-services', [
            'entity'   => $scope->guard(),
            // An office sells the classes of its OWN services, not the whole
            // country catalogue; an unassigned office sees nothing to price and
            // is told to ask the platform rather than shown everybody's.
            'catalog'  => $pricing->catalog(true, $serviceIds),
            'prices'   => $pricing->officePrices($officeId),
            'currency' => ShardManager::currency(),
            'services' => $office?->services ?? collect(),
            'assigned' => $serviceIds !== [],
        ]);
    }
}
