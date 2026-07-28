<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateDriverController extends Controller
{
    public function __invoke(EntityScope $scope, OfficeRepository $offices): View
    {
        return view('panel.drivers.form', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'driver'        => null,
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
        ]);
    }
}
