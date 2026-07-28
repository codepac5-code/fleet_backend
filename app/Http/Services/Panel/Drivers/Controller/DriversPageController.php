<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DriversPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, DriverRepository $drivers, OfficeRepository $offices): View
    {
        $search = trim((string) $request->query('q', ''));
        $officeId = $scope->isAdmin() ? (int) $request->query('office') : null;

        return view('panel.drivers.index', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'search'        => $search,
            'officeFilter'  => $officeId ?: null,
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
            'drivers'       => $drivers->paginate($search ?: null, $officeId ?: null),
        ]);
    }
}
