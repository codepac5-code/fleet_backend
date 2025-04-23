<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Driver;
use App\Models\Office;
use App\Models\Slider;
use App\Models\Address;
use App\Models\Service;
use App\Models\SubService;
use App\Models\PaymentMethod;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {

            DB::beginTransaction();

            // User::factory(10)->create();

            // Address::factory(10)->create();

            // Service::factory(10)->create();

            // Slider::factory(10)->create();

            // SubService::factory(10)->create();

        //    PaymentMethod::factory(4)->create();

            // Office::factory(4)->create();
            Driver::factory(4)->create();


            DB::commit();
        } catch (\Throwable $th) {
            print($th->getMessage());
            DB::rollBack();
            return ;
        }
    }
}
