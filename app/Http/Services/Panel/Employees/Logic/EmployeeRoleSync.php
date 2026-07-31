<?php

namespace App\Http\Services\Panel\Employees\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Models\Employee;
use Throwable;

/**
 * Applies a role's preset to an employee's actual permissions.
 *
 * Best-effort on purpose: on a shard whose permission tables are not provisioned
 * the employee is still created — they simply start with nothing, exactly as
 * before — instead of the whole "add employee" action failing.
 */
class EmployeeRoleSync
{
    public function __construct(private PermissionMatrix $matrix)
    {
    }

    /** Replaces the employee's permissions with their role's preset. */
    public function apply(Employee $employee): bool
    {
        $names = EmployeeRole::permissions($employee->role);

        if ($names === []) {
            return false;
        }

        try {
            $this->matrix->sync($employee, $names, Guard::$Employee);

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /** The preset, narrowed to permissions that exist for the employee guard. */
    public function presetFor(?string $role): array
    {
        $names = EmployeeRole::permissions($role);

        if ($names === []) {
            return [];
        }

        try {
            return array_values(array_intersect($this->matrix->validNames(Guard::$Employee), $names));
        } catch (Throwable $e) {
            return $names;
        }
    }

    /** True when the employee's permissions no longer match their role preset. */
    public function isCustomised(Employee $employee): bool
    {
        try {
            // Spatie keeps the loaded permission relation on the instance, so a
            // model that was just synced would otherwise be compared against the
            // set it had BEFORE the change.
            $employee->unsetRelation('permissions')->unsetRelation('roles');

            $granted = $this->matrix->granted($employee, Guard::$Employee);
        } catch (Throwable $e) {
            return false;
        }

        $preset = $this->presetFor($employee->role);

        sort($granted);
        sort($preset);

        return $granted !== $preset;
    }
}
