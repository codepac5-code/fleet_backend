<?php

namespace App\Http\Services\Panel\Admin\VehicleBrands\Controller;

use App\Http\Controllers\Controller;
use App\Models\VehicleBrand;
use Illuminate\View\View;

class VehicleBrandsPageController extends Controller
{
    public function __invoke(): View
    {
        // Vehicle brands are a GLOBAL reference catalog (like the plan catalog) —
        // a shared list of makes, not any country's operational data. Managing it
        // in the panel is additive; the apps only read brands, never define them.
        return view('panel.vehicle-brands.index', [
            'brands' => VehicleBrand::query()->orderBy('name')->get(),
        ]);
    }
}
