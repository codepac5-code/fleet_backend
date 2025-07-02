<?php

namespace Database\Seeders;

use App\Models\ParentPermission;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
               app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

               $guardName = 'web';
       
               $permissions = [
                // user permissions 
                   'user'=>[
                        'add user',
                        'delete user',
                        'update user',
                        'view user list'
                   ],

                // driver permissions 
                'driver'=>[
                    'add driver',
                    'delete driver',
                    'update driver',
                    'view driver list',
                    'assign permissions',
               ],
                // office permissions 
                'office'=>[
                    'add office',
                    'delete office',
                    'update office',
                    'view office list'
                ],
                'dashboard'=>[
                    'view dashboard',
                    'view roles',

                ]

               ];
       
               foreach ($permissions as $parent_permission => $permissions) {
                $p = ParentPermission::firstOrCreate(
                    ['name' => $parent_permission , 'guard_name' => $guardName]
                );
                    foreach($permissions as $permission ) {
                        Permission::firstOrCreate(
                            ['name' => $permission, 'guard_name' => $guardName ,'parent_id' =>$p->id]
                        );
                    }
                  
               }
       
               $roles = [
                   'super-admin' => [
                    'add office',
                    'delete office',
                    'update office',
                    'view office list'
                   ],
                   'office' => [
                       'view dashboard',
                       'add driver',
                       'delete driver',
                       'update driver',
                       'view driver list',
                       'assign permissions',
                   ],
                   'driver' => [
                       'view dashboard',
                   ],
               ];
       
               foreach ($roles as $roleName => $rolePermissions) {
                   $role = Role::firstOrCreate(
                       ['name' => $roleName, 'guard_name' => $guardName]
                   );
                         $role->syncPermissions($rolePermissions);
            }
    }
}
