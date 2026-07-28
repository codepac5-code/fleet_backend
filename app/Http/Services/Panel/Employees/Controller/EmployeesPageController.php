<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EmployeesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, EmployeeRepository $employees, OfficeRepository $offices): View
    {
        $search = trim((string) $request->query('q', ''));
        $officeId = $scope->isAdmin() ? (int) $request->query('office') : null;

        return view('panel.employees.index', [
            'entity'        => $scope->guard(),
            'user'          => $scope->user(),
            'isAdmin'       => $scope->isAdmin(),
            'search'        => $search,
            'officeFilter'  => $officeId ?: null,
            'officeOptions' => $scope->isAdmin() ? $offices->options() : [],
            'employees'     => $employees->paginate($search ?: null, $officeId ?: null),
        ]);
    }
}
