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
        // Spatie caches the permission catalog in the shared cache store, so when
        // this seeder runs for a SECOND shard in the same deploy the cache is warm
        // with another database's rows and `findOrCreate` skips them — leaving the
        // new shard with ZERO permissions. Drop the cache first so lookups read
        // the shard we're actually seeding.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

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
