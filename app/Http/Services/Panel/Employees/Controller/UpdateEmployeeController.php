<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Employees\Request\UpdateEmployeeRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class UpdateEmployeeController extends Controller
{
    public function __invoke(UpdateEmployeeRequest $request, int $employee, EntityScope $scope, EmployeeRepository $employees): RedirectResponse
    {
        $model = $employees->findOrFail($employee);

        $data = $request->payload();

        if (! $scope->isAdmin()) {
            unset($data['officeId']);
        }

        $employees->update($model, $data);

        return redirect()
            ->route("panel.{$scope->guard()}.employee.index")
            ->with('status', textByLanguage('تم تحديث الموظف بنجاح', 'Employee updated successfully'));
    }
}
