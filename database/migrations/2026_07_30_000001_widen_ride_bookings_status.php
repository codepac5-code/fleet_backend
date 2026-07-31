<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `ride_bookings.status` was `varchar(16)`, and two of its own states are longer:
 * `pending_acceptance` (18) and `no_driver_expired` (17). MySQL was not in strict
 * mode, so it silently stored `pending_acceptan` / `no_driver_expire` — and every
 * comparison against the constant then failed. The office-mediated fixed trip was
 * the casualty: the rider's booking never appeared on the panel's scheduled board
 * (`whereIn('status', …)` matched nothing), the SLA sweep skipped it because its
 * status guard did not recognise the row, and the office could not accept what it
 * could not see. Two characters took out a whole lifecycle.
 */
return new class extends Migration
{
    private const TRUNCATED = [
        'pending_acceptan' => 'pending_acceptance',
        'no_driver_expire' => 'no_driver_expired',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('ride_bookings')) {
            return;
        }

        DB::statement("ALTER TABLE `ride_bookings` MODIFY `status` VARCHAR(32) NOT NULL DEFAULT 'matching'");

        foreach (self::TRUNCATED as $stored => $intended) {
            DB::table('ride_bookings')->where('status', $stored)->update(['status' => $intended]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ride_bookings')) {
            return;
        }

        // Truncate back deliberately rather than let MySQL do it silently again.
        foreach (self::TRUNCATED as $stored => $intended) {
            DB::table('ride_bookings')->where('status', $intended)->update(['status' => $stored]);
        }

        DB::statement("ALTER TABLE `ride_bookings` MODIFY `status` VARCHAR(16) NOT NULL DEFAULT 'matching'");
    }
};
