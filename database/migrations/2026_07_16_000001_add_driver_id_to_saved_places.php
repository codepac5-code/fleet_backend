<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Driver saved places share the `saved_places` table via a nullable `driver_id`
 * (rider rows keep `user_id`; driver rows use `driver_id`). Keeps one table +
 * one model for both owners.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_places') && ! Schema::hasColumn('saved_places', 'driver_id')) {
            Schema::table('saved_places', function (Blueprint $table) {
                $table->unsignedBigInteger('driver_id')->nullable()->after('user_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('saved_places', 'driver_id')) {
            Schema::table('saved_places', function (Blueprint $table) {
                $table->dropColumn('driver_id');
            });
        }
    }
};
