<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_presence') || Schema::hasColumn('driver_presence', 'busy_reason')) {
            return;
        }

        Schema::table('driver_presence', function (Blueprint $table) {
            $table->string('busy_reason', 16)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('driver_presence') && Schema::hasColumn('driver_presence', 'busy_reason')) {
            Schema::table('driver_presence', function (Blueprint $table) {
                $table->dropColumn('busy_reason');
            });
        }
    }
};
