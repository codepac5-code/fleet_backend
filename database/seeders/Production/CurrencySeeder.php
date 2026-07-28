<?php

namespace Database\Seeders\Production;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    private const CURRENCIES = [
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'is_default' => true],
        ['code' => 'SYP', 'name' => 'Syrian Pound', 'symbol' => 'ل.س', 'is_default' => false],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'is_default' => false],
        ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س', 'is_default' => false],
        ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'د.إ', 'is_default' => false],
    ];

    public function run(): void
    {
        foreach (self::CURRENCIES as $currency) {
            Currency::query()->firstOrCreate(
                ['code' => $currency['code']],
                [
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                    'decimals' => 2,
                    'exchange_rate' => 1,
                    'is_default' => $currency['is_default'],
                    'is_active' => true,
                ]
            );
        }
    }
}
