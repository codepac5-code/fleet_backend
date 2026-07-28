<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fixed trips are priced by CORRIDOR (a departure city → arrival city pair with
 * an office-set flat `trip_price` in `travel_routes`), not by distance. Record
 * the corridor + the sub-service on the trip's meta so the status screen, the
 * next-office fallback on decline, and any re-quote all resolve the same
 * corridor the rider booked.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fixed_trip_meta')) {
            return;
        }

        Schema::table('fixed_trip_meta', function (Blueprint $table) {
            if (! Schema::hasColumn('fixed_trip_meta', 'sub_service_id')) {
                $table->unsignedBigInteger('sub_service_id')->nullable()->after('booking_id');
            }
            if (! Schema::hasColumn('fixed_trip_meta', 'departure_city_id')) {
                $table->unsignedBigInteger('departure_city_id')->nullable()->after('sub_service_id');
            }
            if (! Schema::hasColumn('fixed_trip_meta', 'arrival_city_id')) {
                $table->unsignedBigInteger('arrival_city_id')->nullable()->after('departure_city_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('fixed_trip_meta')) {
            return;
        }

        Schema::table('fixed_trip_meta', function (Blueprint $table) {
            foreach (['sub_service_id', 'departure_city_id', 'arrival_city_id'] as $col) {
                if (Schema::hasColumn('fixed_trip_meta', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
