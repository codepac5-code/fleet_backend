<?php

namespace Database\Seeders\Production;

use App\Http\Core\Const\Options\Roles;
use App\Http\Services\Panel\Shared\Authorization\PanelPermission;
use Illuminate\Database\Seeder;
use ReflectionClass;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    private const GUARDS = ['admin', 'office', 'employee'];

    public function run(): void
    {
        $permissions = array_values((new ReflectionClass(PanelPermission::class))->getConstants());

        foreach (self::GUARDS as $guard) {
            foreach ($permissions as $name) {
                Permission::findOrCreate($name, $guard);
            }
        }

        $this->role(Roles::Super_Admin->value, 'admin');
        $this->role(Roles::Office->value, 'office');
        $this->role(Roles::Employee->value, 'employee');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function role(string $name, string $guard): void
    {
        $role = Role::findOrCreate($name, $guard);
        $role->syncPermissions(Permission::query()->where('guard_name', $guard)->get());
    }
}
