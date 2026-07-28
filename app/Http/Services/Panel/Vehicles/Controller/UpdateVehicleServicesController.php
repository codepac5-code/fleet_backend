<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Vehicles\Logic\VehicleRepository;
use App\Http\Services\Panel\Vehicles\Logic\VehicleServiceRepository;
use App\Http\Services\Panel\Vehicles\Request\UpdateVehicleServicesRequest;
use Illuminate\Http\RedirectResponse;

class UpdateVehicleServicesController extends Controller
{
    public function __invoke(UpdateVehicleServicesRequest $request, int $vehicle, EntityScope $scope, VehicleRepository $vehicles, VehicleServiceRepository $services): RedirectResponse
    {
        $model = $vehicles->findOrFail($vehicle);

        $services->sync($model->id, $request->selected());

        return redirect()
            ->route("panel.{$scope->guard()}.vehicle.index")
            ->with('status', textByLanguage('تم تحديث خدمات المركبة', 'Vehicle services updated'));
    }
}
