<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ride_bookings') || Schema::hasColumn('ride_bookings', 'scheduled_at')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('status')->index();
            $table->unsignedSmallInteger('passengers')->nullable()->after('scheduled_at');
            $table->unsignedSmallInteger('luggage')->nullable()->after('passengers');
            $table->string('flight_no', 16)->nullable()->after('luggage');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('ride_bookings') && Schema::hasColumn('ride_bookings', 'scheduled_at')) {
            Schema::table('ride_bookings', function (Blueprint $table) {
                $table->dropColumn(['scheduled_at', 'passengers', 'luggage', 'flight_no']);
            });
        }
    }
};
