<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-driver commission override, as a PERCENTAGE (18.00 = 18%) to match every
 * other rate in the settlement path. Commission is otherwise resolved per OFFICE
 * (subscription plan → fleet rate + office rate), which leaves no way to give a
 * single driver different terms. Null = follow the office, as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('drivers') && ! Schema::hasColumn('drivers', 'commission_rate_override')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->decimal('commission_rate_override', 5, 2)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'commission_rate_override')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('commission_rate_override');
            });
        }
    }
};
