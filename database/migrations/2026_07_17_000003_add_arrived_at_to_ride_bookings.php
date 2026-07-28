<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Capture when the driver reached the pickup (`POST /driver/trips/{id}/arrived`)
 * so on-time-pickup can be measured against a scheduled booking's `scheduled_at`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ride_bookings') || Schema::hasColumn('ride_bookings', 'arrived_at')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->timestamp('arrived_at')->nullable()->after('assigned_at');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('ride_bookings') && Schema::hasColumn('ride_bookings', 'arrived_at')) {
            Schema::table('ride_bookings', function (Blueprint $table) {
                $table->dropColumn('arrived_at');
            });
        }
    }
};
