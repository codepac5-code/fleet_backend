<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class BookingSeeder extends Seeder{

        public function run()
        {
            DB::table('bookings')->delete();
    
            $faker = Faker::create('ar_SA');
    
            $statuses = ['Hold', 'Ongoing', 'Cancelled', 'Completed'];
            $users = range(50, 66);
            $drivers = range(11, 32);
            $subServices = range(1, 15);
            $offices = [1, 2, 3, 4, 5, null];
    
            $dubaiAreas = [
                ['name' => 'وسط مدينة دبي', 'lat' => 25.2048, 'lng' => 55.2708],
                ['name' => 'مرسى دبي', 'lat' => 25.0772, 'lng' => 55.1433],
                ['name' => 'جميرا', 'lat' => 25.1950, 'lng' => 55.2550],
                ['name' => 'خليج الأعمال', 'lat' => 25.2120, 'lng' => 55.2760],
                ['name' => 'البرشاء', 'lat' => 25.1186, 'lng' => 55.2008],
                ['name' => 'ديرة', 'lat' => 25.2630, 'lng' => 55.3080],
                ['name' => 'بر دبي', 'lat' => 25.2590, 'lng' => 55.3070],
                ['name' => 'جبل علي', 'lat' => 24.9850, 'lng' => 55.0970],
                ['name' => 'واحة دبي للسيليكون', 'lat' => 25.0665, 'lng' => 55.3195],
                ['name' => 'نخلة جميرا', 'lat' => 25.1120, 'lng' => 55.1380],
                ['name' => 'برج خليفة', 'lat' => 25.1972, 'lng' => 55.2744],
                ['name' => 'دبي مول', 'lat' => 25.1985, 'lng' => 55.2796],
                ['name' => 'مول الإمارات', 'lat' => 25.1186, 'lng' => 55.2008],
                ['name' => 'سوق البحار', 'lat' => 25.1966, 'lng' => 55.2723],
                ['name' => 'سوق الذهب', 'lat' => 25.2635, 'lng' => 55.3050],
                ['name' => 'ابن بطوطة مول', 'lat' => 25.0670, 'lng' => 55.2210],
                ['name' => 'سيتي سنتر مردف', 'lat' => 25.2420, 'lng' => 55.3670],
                ['name' => 'سيتي سنتر ديرة', 'lat' => 25.2630, 'lng' => 55.3080],
                ['name' => 'دبي فستيفال مول', 'lat' => 25.2530, 'lng' => 55.3640]
            ];
    
            $cancelReasons = [
                'تأخير السائق', 'تغيير خطط المستخدم', 'إلغاء الرحلة من قبل المكتب', 
                'ظروف طارئة', 'مشكلة في الدفع', 'رحلة مزدحمة', 'سوء الأحوال الجوية'
            ];
    
            $startDate = Carbon::create(2025, 6, 25);
            $endDate = Carbon::create(2025, 8, 23);

            for ($i = 1; $i <= 100; $i++) {
    
                if ($i <= 77) {
                    $status = 'Completed';
                } else {
                    $status = $faker->randomElement(['Hold', 'Ongoing', 'Cancelled']);
                }
    
                $userId = $faker->randomElement($users);
                $driverId = $faker->randomElement($drivers);
                $subServiceId = $faker->randomElement($subServices);
                $officeId = $faker->randomElement($offices);
    
                $startAt = $faker->dateTimeBetween($startDate, $endDate);
                $endAt = clone $startAt;
                $endAt->modify('+'.rand(15, 120).' minutes');
    
                $startAreaData = $faker->randomElement($dubaiAreas);
                $endAreaData = $faker->randomElement($dubaiAreas);
    
                while ($endAreaData['name'] === $startAreaData['name']) {
                    $endAreaData = $faker->randomElement($dubaiAreas);
                }
    
                $distance = $faker->randomFloat(2, 2, 50);
                $amount = $faker->randomFloat(2, 20, 300);
                $discount = $faker->randomFloat(2, 0, 50);
    
                DB::table('bookings')->insert([
                    'startAt' => $startAt,
                    'endAt' => $endAt,
                    'amount' => $amount,
                    'discount' => $discount,
                    'time' => $startAt->format('H:i'),
                    'isPercentage' => 1,
                    'totalAmount' => max(0, $amount - $discount),
                    'description' => $faker->sentence(),
                    'rating' => $status === 'Completed' ? rand(3,5) : null,
                    'reason' => $status === 'Cancelled' ? $faker->randomElement($cancelReasons) : null,
                    'couponId' => $faker->optional()->numberBetween(1,50),
                    'status' => $status,
                    'startAddress' => $startAreaData['name'],
                    'endAddress' => $endAreaData['name'],
                    'startLatitude' => $startAreaData['lat'],
                    'startLongitude' => $startAreaData['lng'],
                    'endLatitude' => $endAreaData['lat'],
                    'endLongitude' => $endAreaData['lng'],
                    'distance' => $distance,
                    'paymentId' =>rand(3,4),
                    'paymentType' => $faker->randomElement(['cash', 'electronic', 'fleet_wallet']),
                    'durationDiff' => rand(15,120),
                    'officeId' => $officeId,
                    'driverId' => $driverId,
                    'userId' => $userId,
                    'subServiceId' => $subServiceId,
                    'multiDestnationArray' => null,
                    'officeCommissionValue' => rand(5,15),
                    'driverCommissionValue' => rand(5,20),
                    'fleetCommissionValue' => rand(0,10),
                    'driverCommissionPercentage' => rand(0,100),
                    'officeCommissionPercentage' => rand(0,100),
                    'fleetCommissionPercentage' => rand(0,100),
                    'paymentStatus' =>  $status === 'Completed' ? 'paid' :'failed',
                    'PaymentDatetime' => $status === 'Completed' ? $faker->dateTimeBetween($startAt, $endAt) : null,
                    'otherPaymentTransactionDetail' => null,
                    'created_at' => $faker->dateTimeBetween($startAt, $endAt),
                    'updated_at' => now(),
                ]);
        }
    }
}
