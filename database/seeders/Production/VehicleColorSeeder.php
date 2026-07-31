<?php

namespace Database\Seeders\Production;

use App\Models\VehicleColor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Common car colours so the vehicle form has a usable picklist on first boot.
 * Models are left empty on purpose — they follow the brands an operator sells.
 */
class VehicleColorSeeder extends Seeder
{
    private const COLORS = [
        ['name' => 'أبيض', 'name_en' => 'White', 'hex' => '#ffffff'],
        ['name' => 'أسود', 'name_en' => 'Black', 'hex' => '#111111'],
        ['name' => 'فضي', 'name_en' => 'Silver', 'hex' => '#c0c0c0'],
        ['name' => 'رمادي', 'name_en' => 'Grey', 'hex' => '#808080'],
        ['name' => 'أحمر', 'name_en' => 'Red', 'hex' => '#c62828'],
        ['name' => 'أزرق', 'name_en' => 'Blue', 'hex' => '#1565c0'],
        ['name' => 'أخضر', 'name_en' => 'Green', 'hex' => '#2e7d32'],
        ['name' => 'بيج', 'name_en' => 'Beige', 'hex' => '#d7c9a7'],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('vehicle_colors')) {
            return;
        }

        foreach (self::COLORS as $color) {
            VehicleColor::query()->firstOrCreate(
                ['name_en' => $color['name_en']],
                ['name' => $color['name'], 'hex' => $color['hex'], 'status' => true]
            );
        }
    }
}
