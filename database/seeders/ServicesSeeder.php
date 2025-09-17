<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('services')->insert([
            [
                'image' => 'storage/images/service/1.png',
                'status' => true,
                'title' => 'خدمة الفخامة',
                'description' => 'رحلات بسيارات فارهة مع سائقين محترفين.',
                'title_en' => 'Luxury Ride',
                'description_en' => 'Premium rides with luxury vehicles and professional chauffeurs.',
                'travel_service' => false,
            ],
            [
                'image' => 'storage/images/service/2.png',
                'status' => true,
                'title' => 'خدمة السفر',
                'description' => 'رحلات بين الإمارات أو للمطار.',
                'title_en' => 'Travel Service',
                'description_en' => 'Inter-emirate trips and airport transfers.',
                'travel_service' => true,
            ],
            [
                'image' => 'storage/images/service/3.png',
                'status' => true,
                'title' => 'التاكسي الأصفر',
                'description' => 'التاكسي الرسمي في دبي.',
                'title_en' => 'Yellow Taxi',
                'description_en' => 'Dubai official yellow taxi service.',
                'travel_service' => false,
            ],
            [
                'image' => 'storage/images/service/4.png',
                'status' => true,
                'title' => 'أسطول التاكسي',
                'description' => 'خدمة مكاتب وشركات التاكسي.',
                'title_en' => 'Fleet Taxi',
                'description_en' => 'Taxi fleet operated by companies and offices.',
                'travel_service' => false,
            ],
            [
                'image' => 'storage/images/service/5.png',
                'status' => true,
                'title' => 'الرحلات الصديقة للبيئة',
                'description' => 'رحلات بسيارات كهربائية أو هجينة.',
                'title_en' => 'Eco Ride',
                'description_en' => 'Eco-friendly rides with electric or hybrid vehicles.',
                'travel_service' => false,
            ],
        ]);
    }
}
