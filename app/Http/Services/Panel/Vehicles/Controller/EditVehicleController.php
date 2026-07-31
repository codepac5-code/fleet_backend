<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Vehicles\Logic\VehicleCatalog;
use App\Http\Services\Panel\Vehicles\Logic\VehicleRepository;
use Illuminate\Contracts\View\View;

class EditVehicleController extends Controller
{
    public function __invoke(int $vehicle, EntityScope $scope, VehicleRepository $vehicles, OfficeRepository $offices, DriverRepository $drivers, VehicleCatalog $catalog): View
    {
        return view('panel.vehicles.form', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'vehicle'       => $vehicles->findOrFail($vehicle),
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
            'driverOptions' => $drivers->options(),
            'catalog'       => $catalog->suggestions(),
        ]);
    }
}
