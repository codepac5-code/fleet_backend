<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ride_bookings')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('ride_bookings', 'source')) {
                $table->string('source', 16)->default('app')->index()->after('office_id');
            }
            if (!Schema::hasColumn('ride_bookings', 'created_by')) {
                $table->string('created_by', 40)->nullable()->after('source');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('ride_bookings')) {
            return;
        }

        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropColumn(['source', 'created_by']);
        });
    }
};
