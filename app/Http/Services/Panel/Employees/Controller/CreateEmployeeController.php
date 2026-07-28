<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateEmployeeController extends Controller
{
    public function __invoke(EntityScope $scope, OfficeRepository $offices): View
    {
        return view('panel.employees.form', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'employee'      => null,
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
        ]);
    }
}
