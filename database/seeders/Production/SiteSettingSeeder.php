<?php

namespace Database\Seeders\Production;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    private const DEFAULTS = [
        'app_name_en' => 'Fleet',
        'app_name_ar' => 'فليت',
        'brand_primary' => '#312873',
        'brand_secondary' => '#F8A609',
        'default_currency' => 'USD',
        'cancellation_fee_minor' => '500',
        'free_wait_minutes' => '5',
        'otp_ttl_seconds' => '120',
        'dispatch_radius_m' => '5000',
        'dispatch_offer_ttl_s' => '20',
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            if (SiteSetting::val($key, null) === null) {
                SiteSetting::put($key, $value);
            }
        }

        SiteSetting::flush();
    }
}
