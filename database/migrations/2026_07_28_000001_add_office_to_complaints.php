<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complaints carry only a per-shard `booking_id`, so an office had no way to see
 * the complaints filed against it — the panel screen stayed admin-only. An
 * `office_id` discriminator (stamped from the complained-about booking at
 * creation) makes the screen office-scopable the same way lost_items already is.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('complaints') && ! Schema::hasColumn('complaints', 'office_id')) {
            Schema::table('complaints', function (Blueprint $t) {
                $t->unsignedBigInteger('office_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('complaints') && Schema::hasColumn('complaints', 'office_id')) {
            Schema::table('complaints', function (Blueprint $t) {
                $t->dropColumn('office_id');
            });
        }
    }
};
