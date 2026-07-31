<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who takes what out of a fare, per office.
 *
 * The fleet's cut came only from the office's SUBSCRIPTION plan, so in a
 * commission country every office paid the same rate and the platform had no
 * way to agree a different one with a particular office. And the office's own
 * cut had no home at all: `office_rate` defaulted to zero, so unless somebody
 * set a per-driver override by hand, the office earned nothing on its drivers'
 * rides.
 *
 * Both are nullable on purpose — null means "follow the default", not "zero", so
 * an office nobody has negotiated with keeps tracking the platform rate instead
 * of freezing at whatever it was on the day the column was added.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('offices')) {
            return;
        }

        Schema::table('offices', function (Blueprint $table) {
            if (! Schema::hasColumn('offices', 'fleet_commission_rate')) {
                $table->decimal('fleet_commission_rate', 5, 2)->nullable();
            }

            if (! Schema::hasColumn('offices', 'driver_commission_rate')) {
                $table->decimal('driver_commission_rate', 5, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('offices')) {
            return;
        }

        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['fleet_commission_rate', 'driver_commission_rate']);
        });
    }
};
