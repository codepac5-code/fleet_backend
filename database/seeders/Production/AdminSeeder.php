<?php

namespace Database\Seeders\Production;

use App\Http\Core\Const\Options\Roles;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL', 'admin@fleetos.app');

        $admin = Admin::query()->firstOrCreate(
            ['email' => $email],
            [
                'firstName' => env('SEED_ADMIN_FIRST_NAME', 'Super'),
                'lastName' => env('SEED_ADMIN_LAST_NAME', 'Admin'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'ChangeMe!2026')),
            ]
        );

        $role = Role::findOrCreate(Roles::Super_Admin->value, 'admin');

        $admin->syncRoles([$role]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
