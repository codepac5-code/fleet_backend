<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Online-time accumulation on `driver_presence` so the app can show real
 * "online hours" (and derive trips/hour + earnings/hour) without a separate
 * session-log table. `online_since` marks an open session; `online_seconds_today`
 * accumulates closed sessions for `online_date`, reset when the day rolls over.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_presence')) {
            return;
        }

        Schema::table('driver_presence', function (Blueprint $table) {
            if (! Schema::hasColumn('driver_presence', 'online_since')) {
                $table->timestamp('online_since')->nullable()->after('heartbeat_at');
            }
            if (! Schema::hasColumn('driver_presence', 'online_seconds_today')) {
                $table->unsignedInteger('online_seconds_today')->default(0)->after('online_since');
            }
            if (! Schema::hasColumn('driver_presence', 'online_date')) {
                $table->date('online_date')->nullable()->after('online_seconds_today');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('driver_presence')) {
            return;
        }

        Schema::table('driver_presence', function (Blueprint $table) {
            foreach (['online_since', 'online_seconds_today', 'online_date'] as $col) {
                if (Schema::hasColumn('driver_presence', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
