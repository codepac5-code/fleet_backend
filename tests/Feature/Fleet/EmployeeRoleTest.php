<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Options\Guard;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Http\Services\Panel\Employees\Logic\EmployeeRole;
use App\Http\Services\Panel\Employees\Logic\EmployeeRoleSync;
use App\Http\Services\Panel\Shared\Authorization\PanelPermission as Perm;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class EmployeeRoleTest extends FleetTestCase
{
    // The catalog and the pivot live on the PLATFORM connection (employees are
    // per shard but their grants are not) — the same split as production.
    protected array $globalMigrations = [
        '2024_11_03_151720_create_permission_tables.php',
        '2025_06_20_134508_create_parent_permissions_table.php',
    ];

    protected array $tenantMigrations = [
        // employees has an FK to offices.
        '2024_10_29_211028_create_offices_table.php',
        '2025_06_20_103850_create_employees_table.php',
        // In production the platform connection IS the default one, so the
        // catalog is reachable either way; the harness splits them, so both get
        // the tables.
        '2024_11_03_151720_create_permission_tables.php',
        '2025_06_20_134508_create_parent_permissions_table.php',
    ];

    private EmployeeRoleSync $roles;
    private PermissionMatrix $matrix;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matrix = new PermissionMatrix();
        $this->roles = new EmployeeRoleSync($this->matrix);

        // Every panel permission exists for the employee guard AND belongs to a
        // group, exactly as the production seeders leave it — the matrix only
        // keeps names it can reach through a group.
        $previous = DB::getDefaultConnection();

        foreach ([$previous, 'global'] as $connection) {
            DB::setDefaultConnection($connection);

            foreach ((new \ReflectionClass(Perm::class))->getConstants() as $name) {
                Permission::findOrCreate($name, Guard::$Employee);
            }

            (new \Database\Seeders\Production\PermissionGroupSeeder())->run();
        }

        DB::setDefaultConnection($previous);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function employee(string $role): Employee
    {
        return Employee::query()->create([
            'firstName' => 'E', 'lastName' => $role, 'email' => $role . '@office.test',
            'phoneNumber' => '0900', 'password' => 'secret1234', 'gender' => 'male',
            'role' => $role, 'country' => 'SY', 'region' => 'Damascus', 'city' => 'Damascus',
            'address' => 'x', 'isActive' => 1,
            'employeeJobName_en' => 'Agent', 'employeeJobName_ar' => 'وكيل',
            'job_description_en' => '', 'job_description_ar' => '',
        ]);
    }

    public function test_a_viewer_can_see_but_never_change(): void
    {
        $viewer = EmployeeRole::permissions(EmployeeRole::VIEWER);

        $this->assertContains(Perm::VIEW_BOOKING_LIST, $viewer);
        $this->assertContains(Perm::VIEW_DRIVER_LIST, $viewer);

        foreach ([Perm::EDIT_ORDER_STATUS, Perm::ADD_DRIVER, Perm::DELETE_DRIVER, Perm::EDIT_COMMISSION, Perm::ASSIGN_PERMISSIONS] as $write) {
            $this->assertNotContains($write, $viewer, $write . ' is not read-only');
        }
    }

    public function test_the_roles_nest_from_viewer_to_admin(): void
    {
        $viewer = EmployeeRole::permissions(EmployeeRole::VIEWER);
        $agent = EmployeeRole::permissions(EmployeeRole::AGENT);
        $admin = EmployeeRole::permissions(EmployeeRole::ADMIN);

        $this->assertSame([], array_diff($viewer, $agent), 'an agent can do everything a viewer can');
        $this->assertSame([], array_diff($agent, $admin), 'an admin can do everything an agent can');
        $this->assertGreaterThan(count($agent), count($admin));
    }

    public function test_an_agent_runs_operations_but_not_money_or_staff(): void
    {
        $agent = EmployeeRole::permissions(EmployeeRole::AGENT);

        $this->assertContains(Perm::EDIT_ORDER_STATUS, $agent);
        $this->assertContains(Perm::EDIT_DRIVER, $agent);
        $this->assertNotContains(Perm::EDIT_COMMISSION, $agent);
        $this->assertNotContains(Perm::VIEW_PAYMENTS, $agent);
        $this->assertNotContains(Perm::ADD_EMPLOYEE, $agent);
        $this->assertNotContains(Perm::VIEW_SETTINGS, $agent);
    }

    public function test_applying_a_role_grants_its_permissions(): void
    {
        $employee = $this->employee(EmployeeRole::AGENT);

        $this->assertSame([], $this->matrix->granted($employee, Guard::$Employee), 'starts with nothing');

        $this->assertTrue($this->roles->apply($employee));

        $granted = $this->matrix->granted($employee, Guard::$Employee);
        $this->assertContains(Perm::EDIT_ORDER_STATUS, $granted);
        $this->assertNotContains(Perm::EDIT_COMMISSION, $granted);
    }

    public function test_changing_the_role_moves_the_access(): void
    {
        $employee = $this->employee(EmployeeRole::ADMIN);
        $this->roles->apply($employee);
        $this->assertContains(Perm::EDIT_COMMISSION, $this->matrix->granted($employee, Guard::$Employee));

        // Demoted to viewer — the admin-only permissions have to go.
        $employee->role = EmployeeRole::VIEWER;
        $employee->save();
        $this->roles->apply($employee);

        $granted = $this->matrix->granted($employee, Guard::$Employee);
        $this->assertNotContains(Perm::EDIT_COMMISSION, $granted);
        $this->assertNotContains(Perm::ADD_EMPLOYEE, $granted);
        $this->assertContains(Perm::VIEW_BOOKING_LIST, $granted);
    }

    public function test_manual_tweaks_are_reported_as_customised(): void
    {
        $employee = $this->employee(EmployeeRole::VIEWER);
        $this->roles->apply($employee);

        $this->assertFalse($this->roles->isCustomised($employee));

        $this->matrix->sync($employee, [Perm::VIEW_BOOKING_LIST, Perm::EDIT_COMMISSION], Guard::$Employee);

        $this->assertTrue($this->roles->isCustomised($employee));

        // Reset puts them back on the preset.
        $this->roles->apply($employee);
        $this->assertFalse($this->roles->isCustomised($employee));
    }

    public function test_an_unknown_role_grants_nothing(): void
    {
        // The column is an enum of the three roles, so a bogus value cannot even
        // be stored — the guard here is that the preset layer agrees.
        $employee = $this->employee(EmployeeRole::VIEWER);
        $employee->role = 'ghost';

        $this->assertFalse($this->roles->apply($employee));
        $this->assertSame([], $this->matrix->granted($employee, Guard::$Employee));
        $this->assertSame([], EmployeeRole::permissions('ghost'));
        $this->assertFalse(EmployeeRole::isValid('ghost'));
    }
}
