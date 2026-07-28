<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Live-meter fields. The trip meter is accumulated SERVER-SIDE from the driver's
 * GPS pings (trustworthy) rather than trusting a client-reported total:
 *   - trip_started_at  : when the meter started (ON_TRIP) → drives elapsed time
 *   - meter_distance_m : running distance summed from ping-to-ping deltas
 *   - meter_last_lat/lng: the previous ping, to measure the next delta
 * `distance_m`/`duration_s` keep holding the booking-time ESTIMATE (the expected
 * route), so both the live actual and the expected quote coexist.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ride_bookings')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('ride_bookings', 'trip_started_at')) {
                $table->timestamp('trip_started_at')->nullable()->after('arrived_at');
            }
            if (! Schema::hasColumn('ride_bookings', 'meter_distance_m')) {
                $table->unsignedInteger('meter_distance_m')->default(0)->after('duration_s');
            }
            if (! Schema::hasColumn('ride_bookings', 'meter_last_lat')) {
                $table->decimal('meter_last_lat', 10, 7)->nullable()->after('meter_distance_m');
            }
            if (! Schema::hasColumn('ride_bookings', 'meter_last_lng')) {
                $table->decimal('meter_last_lng', 10, 7)->nullable()->after('meter_last_lat');
            }
            // Ties a booking to the sub-service whose catalog rates price it.
            if (! Schema::hasColumn('ride_bookings', 'sub_service_id')) {
                $table->unsignedBigInteger('sub_service_id')->nullable()->index()->after('service_class');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ride_bookings')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            foreach (['trip_started_at', 'meter_distance_m', 'meter_last_lat', 'meter_last_lng'] as $col) {
                if (Schema::hasColumn('ride_bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
