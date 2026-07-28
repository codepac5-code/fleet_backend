<?php

namespace Database\Seeders;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDefaultConnection() === TenantConnection::NAME) {
            $this->call(ShardSeeder::class);

            return;
        }

        $this->call(ProductionSeeder::class);
    }
}
