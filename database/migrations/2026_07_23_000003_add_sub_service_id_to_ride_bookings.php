<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `sub_service_id` — the picked sub-service/class id (e.g. VIP Chauffeur) carried
 * on the booking. The column was added to the create_ride_bookings migration
 * AFTER that migration had already run in existing environments, so the table
 * was missing the column while the RideBooking model + booking-store INSERT
 * reference it — every instant booking that carried a sub-class 500'd with
 * "Unknown column 'sub_service_id'". This follow-up ALTER heals it. Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ride_bookings')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('ride_bookings', 'sub_service_id')) {
                $table->unsignedBigInteger('sub_service_id')->nullable()->after('service_class')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ride_bookings')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('ride_bookings', 'sub_service_id')) {
                $table->dropColumn('sub_service_id');
            }
        });
    }
};
