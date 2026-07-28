<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ride_bookings') || Schema::hasColumn('ride_bookings', 'change_revision')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->unsignedInteger('change_revision')->default(0)->after('held_minor');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('ride_bookings') && Schema::hasColumn('ride_bookings', 'change_revision')) {
            Schema::table('ride_bookings', function (Blueprint $table) {
                $table->dropColumn('change_revision');
            });
        }
    }
};
