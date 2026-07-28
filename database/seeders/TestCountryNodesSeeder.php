<?php

namespace Database\Seeders;

use App\Models\InfrastructureNode;
use Illuminate\Database\Seeder;

class TestCountryNodesSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
            'type'      => 'country',
            'db_host'   => config('database.connections.global.host'),
            'db_port'   => config('database.connections.global.port'),
            'db_name'   => config('database.connections.global.database'),
            'db_user'   => config('database.connections.global.username'),
            'db_pass'   => config('database.connections.global.password'),
            'is_active' => true,
        ];

        InfrastructureNode::updateOrCreate(
            ['country_code' => 'SA'],
            array_merge($base, ['name' => 'Saudi Arabia', 'city' => 'Riyadh'])
        );

        InfrastructureNode::updateOrCreate(
            ['country_code' => 'QA'],
            array_merge($base, ['name' => 'Qatar', 'city' => 'Doha'])
        );
    }
}
