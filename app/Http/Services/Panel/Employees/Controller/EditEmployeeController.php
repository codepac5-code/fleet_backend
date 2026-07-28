<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditEmployeeController extends Controller
{
    public function __invoke(int $employee, EntityScope $scope, EmployeeRepository $employees, OfficeRepository $offices): View
    {
        return view('panel.employees.form', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'employee'      => $employees->findOrFail($employee),
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
        ]);
    }
}
