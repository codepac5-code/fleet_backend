<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Guard;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Employees\Logic\EmployeeRole;
use App\Http\Services\Panel\Employees\Logic\EmployeeRoleSync;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditEmployeePermissionsController extends Controller
{
    public function __invoke(int $employee, EntityScope $scope, EmployeeRepository $employees, PermissionMatrix $matrix, EmployeeRoleSync $roles): View
    {
        $model = $employees->findOrFail($employee);

        return view('panel.employees.permissions', [
            'entity'   => $scope->guard(),
            'user'     => $scope->user(),
            'employee' => $model,
            'groups'   => $matrix->groups(Guard::$Employee),
            'granted'  => $matrix->granted($model, Guard::$Employee),
            // What the role itself grants, so the screen can mark those and say
            // whether this employee has since been tuned away from it.
            'preset' => $roles->presetFor($model->role),
            'roleLabel' => EmployeeRole::label($model->role),
            'roleDescription' => EmployeeRole::description($model->role),
            'customised' => $roles->isCustomised($model),
        ]);
    }
}
