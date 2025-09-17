<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSubServiceSeeder extends Seeder
{
    public function run()
    {
        DB::table('vehicle_sub_services')->truncate(); 

        $vehicles = range(1, 24);
        $subServices = range(1, 13);

        foreach ($vehicles as $vehicleId) {
            $selectedSubServices = $this->getRandomElements($subServices, 6);

            foreach ($selectedSubServices as $subServiceId) {
                DB::table('vehicle_sub_services')->insert([
                    'vehicleId' => $vehicleId,
                    'subServiceId' => $subServiceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('vehicle_sub_services')->insert([
                'vehicleId' => $vehicleId,
                'subServiceId' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('vehicle_sub_services')->insert([
                'vehicleId' => $vehicleId,
                'subServiceId' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function getRandomElements(array $array, int $count): array
    {
        shuffle($array);
        return array_slice($array, 0, $count);
    }
}
