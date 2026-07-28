<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Re-points the Syrian shard's marketplace at real Damascus geography.
 *
 * The shard was seeded from the Qatari template, so its offices carried Doha
 * names, `country = QATAR` and Doha coordinates (25.28, 51.52) while serving
 * riders in Damascus. Office ETA is computed from the office's own position to
 * the pickup, so those coordinates produced ~3600-minute ETAs, and the tariffs
 * quoted SAR for a market that bills in SYP.
 *
 * This UPDATES the existing five office rows in place (it does not delete or
 * insert) so every `service_tariffs` and `office_sub_service_prices` row that
 * references them by id stays intact.
 *
 * Run against the Syrian shard only:
 *   php artisan db:seed --class=SyrianOfficesSeeder --database=country
 */
class SyrianOfficesSeeder extends Seeder
{
    /** Real Damascus districts, keyed by the office id they replace. */
    private const OFFICES = [
        1 => [
            'officeName' => 'Damascus Luxury Fleet',
            'region' => 'Mazzeh',
            'address' => 'Mazzeh, Damascus, Syria',
            'lat' => 33.5020,
            'lng' => 36.2400,
        ],
        2 => [
            'officeName' => 'Al Sham Travel Taxis',
            'region' => 'Abu Rummaneh',
            'address' => 'Abu Rummaneh, Damascus, Syria',
            'lat' => 33.5170,
            'lng' => 36.2870,
        ],
        3 => [
            'officeName' => 'Green Transport Damascus',
            'region' => 'Kafarsouseh',
            'address' => 'Kafarsouseh, Damascus, Syria',
            'lat' => 33.4960,
            'lng' => 36.2610,
        ],
        4 => [
            'officeName' => 'Barada Fleet Services',
            'region' => 'Midan',
            'address' => 'Midan, Damascus, Syria',
            'lat' => 33.4880,
            'lng' => 36.2960,
        ],
        5 => [
            'officeName' => 'Jasmine Chauffeurs',
            'region' => 'Malki',
            'address' => 'Malki, Damascus, Syria',
            'lat' => 33.5150,
            'lng' => 36.2740,
        ],
    ];

    public function run(): void
    {
        foreach (self::OFFICES as $id => $office) {
            DB::table('offices')->where('id', $id)->update($office + [
                'country' => 'SYRIA',
                'city' => 'Damascus',
            ]);
        }

        // The shard bills in Syrian pounds, not Saudi riyals.
        DB::table('service_tariffs')->update(['currency_code' => 'SYP']);
    }
}
