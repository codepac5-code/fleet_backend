<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Services\Logic\PricingRepository;
use App\Http\Services\Panel\Services\Request\UpdateOfficePricingRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateMyServicesController extends Controller
{
    public function __invoke(UpdateOfficePricingRequest $request, EntityScope $scope, PricingRepository $pricing): RedirectResponse
    {
        $officeId = (int) $scope->officeId();

        if ($officeId <= 0) {
            throw new NotFoundHttpException();
        }

        $office = Office::on(TenantConnection::current())->find($officeId);

        $pricing->syncPrices($officeId, $request->rows(), $office !== null ? $office->serviceIds() : []);

        return redirect()
            ->route('panel.' . $scope->guard() . '.services.mine')
            ->with('status', textByLanguage('تم تحديث خدمات مكتبك', 'Your services were updated'));
    }
}
