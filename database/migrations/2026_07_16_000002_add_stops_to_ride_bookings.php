<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mid-trip waypoints (add-stop). Stored as a JSON array of `{lat,lng,title}` on
 * the booking (global connection, same as ride_bookings) — no separate table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ride_bookings') && ! Schema::hasColumn('ride_bookings', 'stops')) {
            Schema::table('ride_bookings', function (Blueprint $table) {
                $table->json('stops')->nullable()->after('dropoff_title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ride_bookings', 'stops')) {
            Schema::table('ride_bookings', function (Blueprint $table) {
                $table->dropColumn('stops');
            });
        }
    }
};
