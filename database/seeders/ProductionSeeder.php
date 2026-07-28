<?php

namespace Database\Seeders;

use Database\Seeders\Production\AdminSeeder;
use Database\Seeders\Production\CurrencySeeder;
use Database\Seeders\Production\InfrastructureNodeSeeder;
use Database\Seeders\Production\RolesAndPermissionsSeeder;
use Database\Seeders\Production\SiteSettingSeeder;
use Database\Seeders\Production\SubscriptionPlanSeeder;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            CurrencySeeder::class,
            SubscriptionPlanSeeder::class,
            SiteSettingSeeder::class,
            InfrastructureNodeSeeder::class,
        ]);
    }
}
