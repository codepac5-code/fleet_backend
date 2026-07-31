<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Employees\Logic\EmployeeRoleSync;
use App\Http\Services\Panel\Employees\Request\StoreEmployeeRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class StoreEmployeeController extends Controller
{
    public function __invoke(StoreEmployeeRequest $request, EntityScope $scope, EmployeeRepository $employees, EmployeeRoleSync $roles): RedirectResponse
    {
        $data = $request->payload();

        if (! $scope->isAdmin()) {
            $data['officeId'] = $scope->officeId();
        }

        $employee = $employees->create($data);

        // The chosen role now grants its permissions immediately — a new
        // employee used to be able to sign in and see nothing at all until
        // someone opened the matrix and ticked boxes by hand.
        $roles->apply($employee);

        return redirect()
            ->route("panel.{$scope->guard()}.employee.index")
            ->with('status', textByLanguage('تمت إضافة الموظف بنجاح', 'Employee created successfully'));
    }
}
