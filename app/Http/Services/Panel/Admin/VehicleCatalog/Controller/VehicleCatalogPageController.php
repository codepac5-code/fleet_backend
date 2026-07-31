<?php

namespace App\Http\Services\Panel\Admin\VehicleCatalog\Controller;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use App\Models\VehicleColor;
use App\Models\VehicleModel;
use Illuminate\View\View;

/**
 * Models + colours in one screen — they are edited together when a new car is
 * added to the fleet, and each list alone is too small for its own page.
 */
class VehicleCatalogPageController extends Controller
{
    public function __invoke(): View
    {
        $brands = VehicleBrand::query()->orderBy('name_en')->get(['id', 'name', 'name_en']);

        return view('panel.vehicle-catalog.index', [
            'brands' => $brands,
            'brandNames' => $brands->pluck('name_en', 'id'),
            'models' => VehicleModel::query()->orderBy('brand_id')->orderBy('name_en')->get(),
            'colors' => VehicleColor::query()->orderBy('name_en')->get(),
        ]);
    }
}
