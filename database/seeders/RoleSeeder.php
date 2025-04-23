<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'edite service']);
        Permission::create(['name' => 'delete service']);
        Permission::create(['name' => 'create service']);

        $role = Role::create(['name' => 'user']);
        $role->givePermissionTo('edit articles');
        $role->givePermissionTo('delete service');
        $role->givePermissionTo('create service');


//         @if(auth()->user()->can('edit articles'))
//     <a href="{{ route('articles.edit', $article) }}">Edit Article</a>
// @endif

// @if(auth()->user()->hasRole('writer'))
//     <p>You are a writer!</p>
// @endif
    }
}
