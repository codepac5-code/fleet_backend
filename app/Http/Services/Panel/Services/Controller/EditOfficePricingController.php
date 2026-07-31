<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeSnapshot;
use App\Http\Services\Panel\Services\Logic\PricingRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditOfficePricingController extends Controller
{
    public function __invoke(int $office, EntityScope $scope, OfficeRepository $offices, PricingRepository $pricing, OfficeSnapshot $snapshot): View
    {
        $model = $offices->findOrFail($office);
        $serviceIds = $model->serviceIds();

        return view('panel.services.pricing', [
            'entity'  => $scope->guard(),
            'user'    => $scope->user(),
            'office'  => $model,
            // Same boundary as the office's own screen: only its services.
            'catalog' => $pricing->catalog(true, $serviceIds),
            'services' => $model->services,
            'assigned' => $serviceIds !== [],
            'prices'  => $pricing->officePrices($model->id),
            // Meter rates alone do not describe what this office sells: its
            // travel lines, its earnings and its subscription belong next to
            // them, otherwise each lives on a screen nobody thinks to open.
            'snapshot' => $snapshot->for($model),
        ]);
    }
}
