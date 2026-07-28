<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Register SYP as a first-class currency (global `currencies` table).
 *
 * The SY tariffs already price in "SYP" as a bare string, but no currency row
 * backed it — so there was nothing carrying its decimals or exchange rate. This
 * adds the row so a USD wallet top-up can be converted to an SYP credit.
 *
 * The rate is seeded UNSET (0): SYP↔USD has no reliable feed and is volatile, so
 * the value is business-owned and set by an admin from the dashboard. The
 * CurrencyConverter refuses to convert while the rate is 0, which fails loudly
 * instead of charging a wrong amount. `exchange_rate` = units of this currency
 * per 1 unit of the default currency.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('currencies')) {
            return;
        }

        $exists = DB::table('currencies')->where('code', 'SYP')->exists();

        if ($exists) {
            return;
        }

        DB::table('currencies')->insert([
            'code' => 'SYP',
            'name' => 'Syrian Pound',
            'symbol' => 'ل.س',
            'decimals' => 2,
            'exchange_rate' => 0, // UNSET — admin must set it before top-up FX works
            'is_default' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (DB::getSchemaBuilder()->hasTable('currencies')) {
            DB::table('currencies')->where('code', 'SYP')->delete();
        }
    }
};
