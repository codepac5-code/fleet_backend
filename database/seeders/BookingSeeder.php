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

            $qatArAreas = [
                ['name' => 'الدوحة', 'lat' => 25.2854, 'lng' => 51.5310],
                ['name' => 'الريان', 'lat' => 25.2700, 'lng' => 51.4300],
                ['name' => 'اللؤلؤة قطر', 'lat' => 25.3667, 'lng' => 51.5414],
                ['name' => 'سوق واقف', 'lat' => 25.2860, 'lng' => 51.5340],
                ['name' => 'المرخية', 'lat' => 25.3100, 'lng' => 51.5220],
                ['name' => 'مدينة خليفة', 'lat' => 25.2630, 'lng' => 51.4100],
                ['name' => 'مركز قطر الوطني', 'lat' => 25.3030, 'lng' => 51.5050],
                ['name' => 'متحف الفن الإسلامي', 'lat' => 25.2960, 'lng' => 51.5270],
                ['name' => 'الغرافة', 'lat' => 25.3167, 'lng' => 51.4890],
                ['name' => 'برج فندق الشيراتون', 'lat' => 25.2850, 'lng' => 51.5300],
                ['name' => 'الدوحة الجديدة', 'lat' => 25.2900, 'lng' => 51.5400],
                ['name' => 'نادي الدوحة', 'lat' => 25.2950, 'lng' => 51.5250],
                ['name' => 'الدوحة كورنيش', 'lat' => 25.2855, 'lng' => 51.5330],
                ['name' => 'مركز المدينة', 'lat' => 25.2870, 'lng' => 51.5315],
                ['name' => 'اللؤلؤة الشمالية', 'lat' => 25.3670, 'lng' => 51.5430],
                ['name' => 'اللؤلؤة الجنوبية', 'lat' => 25.3650, 'lng' => 51.5400],
                ['name' => 'حي المطار', 'lat' => 25.2760, 'lng' => 51.6160],
                ['name' => 'حي الصناعية', 'lat' => 25.2900, 'lng' => 51.5200],
                ['name' => 'مشيرب', 'lat' => 25.2820, 'lng' => 51.5300],
            ];

            $usaAreas = [
            ['name' => 'New York', 'lat' => 40.7128, 'lng' => -74.0060],
            ['name' => 'Los Angeles', 'lat' => 34.0522, 'lng' => -118.2437],
            ['name' => 'Chicago', 'lat' => 41.8781, 'lng' => -87.6298],
            ['name' => 'Houston', 'lat' => 29.7604, 'lng' => -95.3698],
            ['name' => 'Phoenix', 'lat' => 33.4484, 'lng' => -112.0740],
            ['name' => 'Philadelphia', 'lat' => 39.9526, 'lng' => -75.1652],
            ['name' => 'San Antonio', 'lat' => 29.4241, 'lng' => -98.4936],
            ['name' => 'San Diego', 'lat' => 32.7157, 'lng' => -117.1611],
            ['name' => 'Dallas', 'lat' => 32.7767, 'lng' => -96.7970],
            ['name' => 'San Jose', 'lat' => 37.3382, 'lng' => -121.8863],
            ['name' => 'Austin', 'lat' => 30.2672, 'lng' => -97.7431],
            ['name' => 'Jacksonville', 'lat' => 30.3322, 'lng' => -81.6557],
            ['name' => 'Fort Worth', 'lat' => 32.7555, 'lng' => -97.3308],
            ['name' => 'Columbus', 'lat' => 39.9612, 'lng' => -82.9988],
            ['name' => 'San Francisco', 'lat' => 37.7749, 'lng' => -122.4194],
            ['name' => 'Charlotte', 'lat' => 35.2271, 'lng' => -80.8431],
            ['name' => 'Indianapolis', 'lat' => 39.7684, 'lng' => -86.1581],
            ['name' => 'Seattle', 'lat' => 47.6062, 'lng' => -122.3321],
            ['name' => 'Denver', 'lat' => 39.7392, 'lng' => -104.9903],
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

                $startAreaData = $faker->randomElement($qatArAreas);
                $endAreaData = $faker->randomElement($qatArAreas);

                while ($endAreaData['name'] === $startAreaData['name']) {
                    $endAreaData = $faker->randomElement($qatArAreas);
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
