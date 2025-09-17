<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubServicesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sub_services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        DB::table('sub_services')->insert([

            // Luxury Ride
            [
                'id'=>1 ,
                'name' => 'سائق خاص فخم',
                'image' => '/storage/images/service/1.png',
                'status' => true,
                'description' => 'سائق خاص بسيارة فارهة مع خدمات ممتازة.',
                'openPrice' => 50.00,
                'kmPrice' => 5.50,
                'minutePrice' => 2.50,
                'serviceId' => 1,
                'name_en' => 'VIP Chauffeur',
                'description_en' => 'Private chauffeur with a luxury car.',
            ],
            [
                'id'=>2 ,
                'name' => 'رحلات رجال الأعمال',
                'image' => '/storage/images/service/1.png',
                'status' => true,
                'description' => 'رحلات مريحة للمسافرين بغرض الأعمال.',
                'openPrice' => 40.00,
                'kmPrice' => 4.00,
                'minutePrice' => 2.00,
                'serviceId' => 1,
                'name_en' => 'Business Executive',
                'description_en' => 'Business trips with premium sedans.',
            ],
            [
                'id'=>3 ,
                'name' => 'رحلة الزفاف',
                'image' => '/storage/images/service/1.png',
                'status' => true,
                'description' => 'خدمة زفاف مع سيارة فاخرة وسائق محترف.',
                'openPrice' => 100.00,
                'kmPrice' => 6.00,
                'minutePrice' => 3.00,
                'serviceId' => 1,
                'name_en' => 'Wedding Ride',
                'description_en' => 'Luxury wedding ride service.',
            ],

            // Travel Service
            [                
                'id'=>4 ,
                'name' => 'استقبال من المطار',
                'image' => '/storage/images/service/2.png',
                'status' => true,
                'description' => 'خدمة استقبال من المطار مع راحة عالية.',
                'openPrice' => 60.00,
                'kmPrice' => 3.50,
                'minutePrice' => 1.80,
                'serviceId' => 2,
                'name_en' => 'Airport Pickup',
                'description_en' => 'Pickup from Dubai International Airport.',
            ],
            [
                'id'=>5 ,
                'name' => 'رحلات بين الإمارات',
                'image' => '/storage/images/service/2.png',
                'status' => true,
                'description' => 'رحلات بين الإمارات بمركبات مريحة.',
                'openPrice' => 80.00,
                'kmPrice' => 3.00,
                'minutePrice' => 1.50,
                'serviceId' => 2,
                'name_en' => 'Inter-Emirates Trip',
                'description_en' => 'Trips between UAE emirates.',
            ],
            [
                'id'=>6,
                'name' => 'جولات سياحية داخل المدينة',
                'image' => '/storage/images/service/2.png',
                'status' => true,
                'description' => 'جولات سياحية مريحة داخل دبي.',
                'openPrice' => 70.00,
                'kmPrice' => 3.20,
                'minutePrice' => 1.60,
                'serviceId' => 2,
                'name_en' => 'Tourist City Tour',
                'description_en' => 'City sightseeing tours.',
            ],

            // Yellow Taxi
            [
                'id'=>7 ,
                'name' => 'رحلة داخل المدينة',
                'image' => '/storage/images/service/3.png',
                'status' => true,
                'description' => 'خدمة تاكسي قياسية داخل المدينة.',
                'openPrice' => 12.00,
                'kmPrice' => 2.00,
                'minutePrice' => 0.80,
                'serviceId' => 3,
                'name_en' => 'City Ride',
                'description_en' => 'Standard city ride.',
            ],
            [
                'id'=>8 ,
                'name' => 'خدمة ليلية',
                'image' => '/storage/images/service/3.png',
                'status' => true,
                'description' => 'رحلات ليلية بمستوى راحة جيد.',
                'openPrice' => 15.00,
                'kmPrice' => 2.20,
                'minutePrice' => 1.00,
                'serviceId' => 3,
                'name_en' => 'Night Service',
                'description_en' => 'Night taxi service.',
            ],
            [
                'id'=>9 ,
                'name' => 'نقل سريع',
                'image' => '/storage/images/service/3.png',
                'status' => true,
                'description' => 'خدمة نقل سريع داخل المدينة.',
                'openPrice' => 14.00,
                'kmPrice' => 2.10,
                'minutePrice' => 0.90,
                'serviceId' => 3,
                'name_en' => 'Quick Ride',
                'description_en' => 'Fast city taxi service.',
            ],

            // Fleet Taxi
            [
                'id'=>10 ,
                'name' => 'أسطول الشركات المميز',
                'image' => '/storage/images/service/4.png',
                'status' => true,
                'description' => 'سيارة مكيفة مع سائق محترف ودفع إلكتروني للشركات.',
                'openPrice' => 20.00,
                'kmPrice' => 2.50,
                'minutePrice' => 1.20,
                'serviceId' => 4,
                'name_en' => 'Corporate Premium',
                'description_en' => 'Air-conditioned ride with professional driver and corporate payment.',
            ],
            [
                'id'=>11 ,
                'name' => 'رحلة مريحة',
                'image' => '/storage/images/service/4.png',
                'status' => true,
                'description' => 'رحلة مريحة مع مساحة أكبر للركاب ووسائل ترفيه.',
                'openPrice' => 18.00,
                'kmPrice' => 2.40,
                'minutePrice' => 1.10,
                'serviceId' => 4,
                'name_en' => 'Comfort Ride',
                'description_en' => 'Comfortable ride with extra legroom and in-car entertainment.',
            ],
            [
                'id'=>12 ,
                'name' => 'خدمة تنقل مميزة',
                'image' => '/storage/images/service/4.png',
                'status' => true,
                'description' => 'خدمة VIP من وإلى المطار أو المكتب.',
                'openPrice' => 25.00,
                'kmPrice' => 2.60,
                'minutePrice' => 1.30,
                'serviceId' => 4,
                'name_en' => 'Premium Shuttle',
                'description_en' => 'VIP shuttle service to airport or office.',
            ],

            // Eco Ride
            [
                'id'=>13 ,
                'name' => 'تسلا فاخرة',
                'image' => '/storage/images/service/5.png',
                'status' => true,
                'description' => 'رحلة مريحة بسيارة Tesla.',
                'openPrice' => 35.00,
                'kmPrice' => 2.80,
                'minutePrice' => 1.40,
                'serviceId' => 5,
                'name_en' => 'Tesla Premium',
                'description_en' => 'Premium Tesla ride.',
            ],
            [
                'id'=>14 ,
                'name' => 'كهربائية اقتصادية',
                'image' => '/storage/images/service/5.png',
                'status' => true,
                'description' => 'رحلة اقتصادية بسيارة كهربائية.',
                'openPrice' => 25.00,
                'kmPrice' => 2.50,
                'minutePrice' => 1.20,
                'serviceId' => 5,
                'name_en' => 'Budget Electric',
                'description_en' => 'Budget-friendly electric ride.',
            ],
            [
                'id'=>15 ,
                'name' => 'رحلة صديقة للبيئة',
                'image' => '/storage/images/service/5.png',
                'status' => true,
                'description' => 'رحلة صديقة للبيئة مع راحة إضافية.',
                'openPrice' => 30.00,
                'kmPrice' => 2.70,
                'minutePrice' => 1.35,
                'serviceId' => 5,
                'name_en' => 'Eco Comfort',
                'description_en' => 'Eco-friendly ride with extra comfort.',
            ],

        ]);
    }
}
