<?php

namespace App\Http\Services\Panel\Employees\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Employees\Logic\EmployeeRepository;
use App\Http\Services\Panel\Employees\Logic\EmployeeRoleSync;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Puts a hand-tuned employee back on their role's preset — the way out of
 * "nobody remembers why this person can do that".
 */
class ResetEmployeePermissionsController extends Controller
{
    public function __invoke(Request $request, int $employee, EntityScope $scope, EmployeeRepository $employees, EmployeeRoleSync $roles, AuditLogService $audit): RedirectResponse
    {
        $model = $employees->findOrFail($employee);

        if (! $roles->apply($model)) {
            return back()->with('error', textByLanguage('تعذّر تطبيق إعدادات الدور.', 'Could not apply the role defaults.'));
        }

        $audit->record(
            'employee.permissions_reset',
            $scope->guard(),
            $scope->user()?->id,
            'employee',
            (int) $model->id,
            ['role' => $model->role],
            $request->ip()
        );

        return back()->with('status', textByLanguage('تمت إعادة الصلاحيات إلى إعدادات الدور', 'Permissions reset to the role defaults'));
    }
}
