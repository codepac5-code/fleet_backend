<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Guard;
use App\Http\Core\Const\Options\Roles;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Employees\Request\UpdateEmployeePermissionsRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class UpdateEmployeePermissionsController extends Controller
{
    public function __invoke(UpdateEmployeePermissionsRequest $request, int $employee, EntityScope $scope, EmployeeRepository $employees, PermissionMatrix $matrix): RedirectResponse
    {
        $model = $employees->findOrFail($employee);

        $matrix->sync($model, $request->selected(), Guard::$Employee, Roles::Employee->value);

        return redirect()
            ->route("panel.{$scope->guard()}.employee.index")
            ->with('status', textByLanguage('تم تحديث صلاحيات الموظف', 'Employee permissions updated'));
    }
}
