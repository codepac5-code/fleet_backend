<?php

namespace App\Console\Commands;

use App\Http\Core\Const\Options\Guard;
use App\Http\Core\GeoServices\ShardRunner;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Http\Services\Panel\Employees\Logic\EmployeeRoleSync;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Employee;
use Illuminate\Console\Command;
use Throwable;

/**
 * Grants existing employees the permissions their role has always implied.
 *
 * Employees created before roles carried any authority have a role label and an
 * empty permission set — they sign in and see nothing. By default this only
 * touches those; `--force` re-applies the preset to everyone, discarding manual
 * tweaks, which is why it is not the default.
 */
class SyncEmployeeRoles extends Command
{
    protected $signature = 'fleet:employee-roles-sync
        {--force : also re-apply to employees who already have permissions, discarding manual tweaks}
        {--dry-run : report what would change without writing}';

    protected $description = 'Apply each employee role preset (agent/admin/viewer) to employees, per country.';

    public function handle(EmployeeRoleSync $roles, PermissionMatrix $matrix): int
    {
        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        ShardRunner::eachCountry(function ($node) use ($roles, $matrix, $dry, $force) {
            $country = $node->country_code ?? $node->id;

            try {
                $employees = Employee::on(TenantConnection::current())->get();
            } catch (Throwable $e) {
                $this->warn(sprintf('[%s] skipped: %s', $country, $e->getMessage()));

                return;
            }

            $applied = 0;
            $skipped = 0;

            foreach ($employees as $employee) {
                $granted = [];

                try {
                    $granted = $matrix->granted($employee, Guard::$Employee);
                } catch (Throwable $e) {
                }

                if ($granted !== [] && ! $force) {
                    $skipped++;
                    continue;
                }

                if (! $dry && ! $roles->apply($employee)) {
                    $skipped++;
                    continue;
                }

                $applied++;
            }

            $this->line(sprintf(
                '%s[%s] %d employees: %d granted their role preset, %d left alone',
                $dry ? '[dry-run] ' : '',
                $country,
                $employees->count(),
                $applied,
                $skipped
            ));
        });

        return self::SUCCESS;
    }
}
