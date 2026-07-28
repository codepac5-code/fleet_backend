<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Guard;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditEmployeePermissionsController extends Controller
{
    public function __invoke(int $employee, EntityScope $scope, EmployeeRepository $employees, PermissionMatrix $matrix): View
    {
        $model = $employees->findOrFail($employee);

        return view('panel.employees.permissions', [
            'entity'   => $scope->guard(),
            'user'     => $scope->user(),
            'employee' => $model,
            'groups'   => $matrix->groups(Guard::$Employee),
            'granted'  => $matrix->granted($model, Guard::$Employee),
        ]);
    }
}
