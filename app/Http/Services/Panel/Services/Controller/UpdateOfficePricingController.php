<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Services\Logic\PricingRepository;
use App\Http\Services\Panel\Services\Request\UpdateOfficePricingRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class UpdateOfficePricingController extends Controller
{
    public function __invoke(UpdateOfficePricingRequest $request, int $office, EntityScope $scope, OfficeRepository $offices, PricingRepository $pricing): RedirectResponse
    {
        $model = $offices->findOrFail($office);

        $pricing->syncPrices($model->id, $request->rows(), $model->serviceIds());

        return redirect()
            ->route('panel.admin.office.index')
            ->with('status', textByLanguage('تم تحديث تسعير المكتب', 'Office pricing updated'));
    }
}
