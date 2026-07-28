<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficeRequestSeeder extends Seeder
{
    public function run()
    {
        DB::table('office_requests')->insert([

            [
                'office_name' => 'Alpha Transport',
                'contact_name' => 'Ahmad Ali',
                'email' => 'alpha@test.com',
                'phone' => '+31612345678',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'website' => 'https://alpha.com',

                'business_category' => 'Existing',
                'fleet_size' => 25,
                'service_type' => 'City',
                'current_tools' => 'Excel',
                'coverage' => 'Rotterdam',

                'license_status' => 'Yes',
                'timeline' => 'Immediate',
                'notes' => 'Interested in expansion',

                'status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'office_name' => 'SkyLine Cars',
                'contact_name' => 'Mohamed Hassan',
                'email' => 'sky@test.com',
                'phone' => '+49123456789',
                'city' => 'Berlin',
                'country' => 'Germany',
                'website' => 'https://skyline.com',

                'business_category' => 'Corporate',
                'fleet_size' => 60,
                'service_type' => 'Airport',
                'current_tools' => 'CRM System',
                'coverage' => 'Berlin',

                'license_status' => 'Yes',
                'timeline' => '30 days',
                'notes' => 'Corporate contracts',

                'status' => 'reviewed',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'office_name' => 'Fast Ride',
                'contact_name' => 'Omar Khaled',
                'email' => 'fast@test.com',
                'phone' => '+971501234567',
                'city' => 'Dubai',
                'country' => 'UAE',
                'website' => 'https://fastride.com',

                'business_category' => 'New',
                'fleet_size' => 10,
                'service_type' => 'Mixed',
                'current_tools' => null,
                'coverage' => 'Dubai',

                'license_status' => 'No',
                'timeline' => 'Exploring',
                'notes' => 'Startup phase',

                'status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
