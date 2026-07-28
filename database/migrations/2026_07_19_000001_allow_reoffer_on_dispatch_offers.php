<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A driver whose offer merely timed out must be reachable again in a later
 * wave — in a thin market the old `unique(booking_id, driver_id)` permanently
 * burned every candidate after one pass. Offers become an append-only log; the
 * "don't pester" rule now lives in DispatchService::offerWave, which still
 * excludes drivers who rejected, accepted, or hold an open offer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatch_offers', function (Blueprint $table) {
            $table->dropUnique('dispatch_offers_booking_driver_unique');
            // Keep the lookup fast now that (booking_id, driver_id) may repeat.
            $table->index(['booking_id', 'driver_id'], 'dispatch_offers_booking_driver_idx');
        });
    }

    public function down(): void
    {
        Schema::table('dispatch_offers', function (Blueprint $table) {
            $table->dropIndex('dispatch_offers_booking_driver_idx');
            $table->unique(['booking_id', 'driver_id'], 'dispatch_offers_booking_driver_unique');
        });
    }
};
