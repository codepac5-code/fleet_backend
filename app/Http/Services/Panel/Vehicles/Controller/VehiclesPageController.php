<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Vehicles\Logic\VehicleRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VehiclesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, VehicleRepository $vehicles, OfficeRepository $offices, DriverRepository $drivers): View
    {
        $search = trim((string) $request->query('q', ''));
        $officeId = $scope->isAdmin() ? (int) $request->query('office') : null;

        return view('panel.vehicles.index', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'search'        => $search,
            'officeFilter'  => $officeId ?: null,
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
            'driverOptions' => $drivers->options(),
            'vehicles'      => $vehicles->paginate($search ?: null, $officeId ?: null),
        ]);
    }
}
