<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ride_bookings') || Schema::hasColumn('ride_bookings', 'dropoff_title')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->string('pickup_title')->nullable()->after('pickup_note');
            $table->string('dropoff_title')->nullable()->after('dropoff_lng');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('ride_bookings') && Schema::hasColumn('ride_bookings', 'dropoff_title')) {
            Schema::table('ride_bookings', function (Blueprint $table) {
                $table->dropColumn(['pickup_title', 'dropoff_title']);
            });
        }
    }
};
