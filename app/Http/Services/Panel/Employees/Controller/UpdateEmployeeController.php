<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Employees\Logic\EmployeeRoleSync;
use App\Http\Services\Panel\Employees\Request\UpdateEmployeeRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class UpdateEmployeeController extends Controller
{
    public function __invoke(UpdateEmployeeRequest $request, int $employee, EntityScope $scope, EmployeeRepository $employees, EmployeeRoleSync $roles): RedirectResponse
    {
        $model = $employees->findOrFail($employee);

        $data = $request->payload();

        if (! $scope->isAdmin()) {
            unset($data['officeId']);
        }

        $previousRole = $model->role;
        $employees->update($model, $data);

        // Changing someone's role is the one edit that MUST move their access —
        // otherwise a demoted admin keeps every permission. Editing anything
        // else leaves hand-tuned permissions alone.
        $roleChanged = $model->role !== $previousRole;

        if ($roleChanged) {
            $roles->apply($model);
        }

        return redirect()
            ->route("panel.{$scope->guard()}.employee.index")
            ->with('status', $roleChanged
                ? textByLanguage('تم تحديث الموظف وتطبيق صلاحيات الدور الجديد', 'Employee updated and the new role\'s permissions applied')
                : textByLanguage('تم تحديث الموظف بنجاح', 'Employee updated successfully'));
    }
}
