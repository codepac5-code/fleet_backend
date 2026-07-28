<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateVehicleController extends Controller
{
    public function __invoke(EntityScope $scope, OfficeRepository $offices, DriverRepository $drivers): View
    {
        return view('panel.vehicles.form', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'vehicle'       => null,
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
            'driverOptions' => $drivers->options(),
        ]);
    }
}
