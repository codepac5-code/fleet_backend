<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Vehicles\Logic\VehicleRepository;
use App\Http\Services\Panel\Vehicles\Logic\VehicleServiceRepository;
use Illuminate\Contracts\View\View;

class EditVehicleServicesController extends Controller
{
    public function __invoke(int $vehicle, EntityScope $scope, VehicleRepository $vehicles, VehicleServiceRepository $services): View
    {
        $model = $vehicles->findOrFail($vehicle);

        return view('panel.vehicles.services', [
            'entity'   => $scope->guard(),
            'user'     => $scope->user(),
            'vehicle'  => $model,
            'catalog'  => $services->catalog(),
            'assigned' => $services->assignedIds($model->id),
        ]);
    }
}
