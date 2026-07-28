<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional photo for a driver-reported found item (`POST /driver/trips/{id}/
 * found-items`). Stored on the public disk; the column holds its URL/path.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lost_items') && ! Schema::hasColumn('lost_items', 'photo')) {
            Schema::table('lost_items', function (Blueprint $table) {
                $table->string('photo', 512)->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lost_items') && Schema::hasColumn('lost_items', 'photo')) {
            Schema::table('lost_items', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};
