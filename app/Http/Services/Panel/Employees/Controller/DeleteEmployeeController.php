<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class DeleteEmployeeController extends Controller
{
    public function __invoke(int $employee, EntityScope $scope, EmployeeRepository $employees): RedirectResponse
    {
        $employees->delete($employees->findOrFail($employee));

        return redirect()
            ->route("panel.{$scope->guard()}.employee.index")
            ->with('status', textByLanguage('تم حذف الموظف', 'Employee deleted'));
    }
}
