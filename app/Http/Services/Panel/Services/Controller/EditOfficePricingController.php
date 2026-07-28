<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Services\Logic\PricingRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditOfficePricingController extends Controller
{
    public function __invoke(int $office, EntityScope $scope, OfficeRepository $offices, PricingRepository $pricing): View
    {
        $model = $offices->findOrFail($office);

        return view('panel.services.pricing', [
            'entity'  => $scope->guard(),
            'user'    => $scope->user(),
            'office'  => $model,
            'catalog' => $pricing->catalog(true),
            'prices'  => $pricing->officePrices($model->id),
        ]);
    }
}
