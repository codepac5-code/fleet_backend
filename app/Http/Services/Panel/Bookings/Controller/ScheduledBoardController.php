<?php

namespace App\Http\Services\Panel\Bookings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class ScheduledBoardController extends Controller
{
    public function __invoke(
        EntityScope $scope,
        DriverRepository $drivers,
        OfficeRepository $offices
    ): View {
        return view('panel.bookings.scheduled', [
            'entity'        => $scope->guard(),
            'isAdmin'       => $scope->isAdmin(),
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
            'drivers'       => $drivers->assignable(),
        ]);
    }
}
