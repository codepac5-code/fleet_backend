<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which sub-services an office actually offers.
 *
 * Participation used to be implied by the mere EXISTENCE of a price row: the
 * rider search collects offices from `office_sub_service_prices`, so an office
 * that left a price blank (documented on the pricing screen as "use the base
 * price") silently vanished from that service instead of inheriting the base.
 * An explicit flag separates the two questions — "do you offer this?" and "at
 * what price?" — and existing rows stay enabled, which is what they meant.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('office_sub_service_prices') && ! Schema::hasColumn('office_sub_service_prices', 'is_enabled')) {
            Schema::table('office_sub_service_prices', function (Blueprint $table) {
                $table->boolean('is_enabled')->default(true)->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('office_sub_service_prices') && Schema::hasColumn('office_sub_service_prices', 'is_enabled')) {
            Schema::table('office_sub_service_prices', function (Blueprint $table) {
                $table->dropColumn('is_enabled');
            });
        }
    }
};
