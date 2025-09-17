<?php

namespace Database\Seeders;

use App\Http\Core\Const\Options\Guard;
use App\Http\Core\Const\Options\Roles;
use App\Models\Admin;
use App\Models\ParentPermission;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Schema::disableForeignKeyConstraints();

        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('parent_permissions')->truncate();

        Schema::enableForeignKeyConstraints();

    

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guardNames = [
            Guard::$Admin,
            Guard::$Office,
            Guard::$Employee,
        ];

        $permissions = [
            'service' => [
                'view service list',
                'add service',
                'edit service',
                'delete service',
            ],
            'sub-service' => [
                'view sub-service list',
                'add sub-service',
                'edit sub-service',
                'delete sub-service',
            ],
            'user' => [
                'add user',
                'delete user',
                'edit user',
                'view user list',
            ],
            'driver' => [
                'driver change custom commission',
                'add driver',
                'delete driver',
                'edit driver',
                'view driver list',
                'assign permissions',
                'view drivers new style',
            ],
            'office' => [
                'office change custom commission',
                'add office',
                'delete office',
                'update office',
                'view office list',
                'office overview',
            ],
            'employee' => [
                'add employee',
                'delete employee',
                'update employee',
                'view employee list',
            ],
            'vehicle' => [
                'add vehicle',
                'delete vehicle',
                'update vehicle',
                'view vehicle list',
            ],
            'dashboard' => [
                'view dashboard',
                'order history',
                'follow orders',
                'view roles',
                'monthly revenue',
                'track drivers locations',
            ],
            'system' => [
                'assign permissions',
                'edit commission',
            ],
            'orders' => [
                'show order details',
                'edit order status',
            ],
            'banners'=> [
                'view banner list',
            ],
            'department' => [
                'add department',
                'delete department',
                'update department',
                'view department list',
            ],
            'issues' =>[
                'issues add',
            ]
        ];

        foreach ($guardNames as $guardName) {
            foreach ($permissions as $parent_permission => $child_permissions) {
                $p = ParentPermission::firstOrCreate([
                    'name' => $parent_permission,
                    'guard_name' => $guardName,
                ]);

                foreach ($child_permissions as $permission) {
                    Permission::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $guardName,
                    ], [
                        'parent_id' => $p->id,
                    ]);
                }
            }
        }

        $super_admin_permissions = [
            // Dashboard
            'view dashboard',
            'order history',
            'follow orders',
            'view roles',
            'monthly revenue',
            'track drivers locations',

            // Vehicle
            'add vehicle',
            'delete vehicle',
            'update vehicle',
            'view vehicle list',

            // Employee
            'add employee',
            'delete employee',
            'update employee',
            'view employee list',

            // Office
            'add office',
            'delete office',
            'edit office',
            'view office list',
            'office overview',

            // Driver
            'add driver',
            'delete driver',
            'edit driver',
            'view driver list',
            'assign permissions',
            'view drivers new style',

            // User
            'add user',
            'delete user',
            'edit user',
            'view user list',

            // Sub-Service
            'view sub-service list',
            'add sub-service',
            'edit sub-service',
            'delete sub-service',

            // Service
            'view service list',
            'add service',
            'edit service',
            'delete service',

            // System
            'view commission',
            'edit commission',


            // order
            'show order details',
            'edit order status',


            'add department',
            'delete department',
            'update department',
            'view department list',

            'issues add',

            'driver change custom commission',

            'office change custom commission',


        ];


        $this->assignPermissionsToRoleManually( Roles::Super_Admin->value , Guard::$Admin,  $super_admin_permissions);
     
        $admins = Admin::all();

        foreach($admins as $admin){
        $admin->assignRole(Roles::Super_Admin->value);    
        }

        // $role->syncPermissions($super_admin_permissions);

        
        // 🟡 Office Manager
        $office_permissions = [
            // Dashboard
            'view dashboard',
            'order history',
            'follow orders',
            'view roles',
            'monthly revenue',
            'track drivers locations',

            // Vehicle
            'add vehicle',
            'delete vehicle',
            'update vehicle',
            'view vehicle list',

            // Employee
            'add employee',
            'delete employee',
            'update employee',
            'view employee list',


            // Driver
            'add driver',
            'delete driver',
            'edit driver',
            'view driver list',
            'assign permissions',
            'view drivers new style',

            
            // System
            'view commission',
            'edit commission',

            'add department',
            'delete department',
            'update department',
            'view department list',

            'issues add',

            'driver change custom commission',

        ];


        $this->assignPermissionsToRoleManually( Roles::Office->value , Guard::$Office,  $office_permissions);








// // 🟢 Office Employee
$emp_permissions = [
    // Dashboard
    'order history',
    'follow orders',

    // Vehicle
    'add vehicle',
    'delete vehicle',
    'update vehicle',
    'view vehicle list',


    // Driver
    'add driver',
    'delete driver',
    'edit driver',
    'view driver list',
    'assign permissions',
    'view drivers new style',

    // User
    // 'add user',
    // 'delete user',
    // 'edit user',
    // 'view user list',

    // order
    'show order details',
    'edit order status',


    'add department',
    'delete department',
    'update department',
    'view department list',

    'issues add',

    'driver change custom commission',
    'view commission',

];

$emp_role = Role::firstOrCreate([
'name' => 'employee',
'guard_name' => Guard::$Employee,
]);

$this->assignPermissionsToRoleManually( Roles::Employee->value , Guard::$Employee,  $emp_permissions);



$demo_admin_permissions = [
    'view service list',
    'view sub-service list',
    'view user list',
    'view driver list',
    'view office list',
    'view employee list',
    'view vehicle list',
    'view dashboard',
    'view roles',
    'view department list',
    'view commission',

];

$demo_admin_role = Role::firstOrCreate([
    'name' => 'demo admin',
    'guard_name' => Guard::$Employee,
]);

$this->assignPermissionsToRoleManually('demo admin', Guard::$Employee, $demo_admin_permissions);
// // 🟠 Fleet Employee
// $fleetEmployeeRole = Role::firstOrCreate([
//     'name' => 'fleet_employee',
//     'guard_name' => Guard::$Employee,
// ]);

// $fleetEmployeeRole->syncPermissions([
//     'add vehicle',
//     'delete vehicle',
//     'update vehicle',
//     'view vehicle list',
//     'view service list',
//     'add service',
//     'edit service',
//     'delete service',
//     'view sub-service list',
//     'add sub-service',
//     'edit sub-service',
//     'delete sub-service',
//     'follow orders',
// ]);

//     }

 }



 function assignPermissionsToRoleManually(string $roleName, string $guardName, array $permissionNames): void {
    $role = Role::firstOrCreate([
        'name' => $roleName,
        'guard_name' => $guardName,
    ]);

    foreach ($permissionNames as $permissionName) {
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => $guardName,
        ]);

        DB::table('role_has_permissions')->updateOrInsert([
            'role_id' => $role->id,
            'permission_id' => $permission->id,
        ]);
    }

    app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
