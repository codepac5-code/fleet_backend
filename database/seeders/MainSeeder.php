<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MainSeeder extends Seeder
{
    /**
     * Run all seeders.
     */
    public function run(): void
    {
        $this->call([
            // RolePermissionSeeder::class,
            // ServicesSeeder::class,
            // SubServicesSeeder::class,
            // OfficesSeeder::class,
            // VehiclesSeeder::class,
            // UsersTableSeeder::class,
            // DriversSeeder::class,
            // EmployeeSeeder::class,
            // OfficeCustomers::class,
            // BookingSeeder::class,
            // VehicleSubServiceSeeder::class,
            RatingsSeeder::class,
            

        ]);
    }
}
