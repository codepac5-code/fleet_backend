<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconcile the `drivers` table with the Driver model + DriverX app contract.
 *
 * Driver.php $fillable declares `email`, `userName`, `status` and $casts
 * references `email_verified_at`, but 2024_11_10_085532_create_drivers_table
 * never created them (model <-> DB drift — see docs/BACKEND_HANDOFF_NOTES.md A2).
 * The account screen needs an email, and account lifecycle needs a status.
 *
 * Idempotent: guarded by hasColumn so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (! Schema::hasColumn('drivers', 'email')) {
                $table->string('email')->nullable()->unique()->after('lastName');
            }
            if (! Schema::hasColumn('drivers', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (! Schema::hasColumn('drivers', 'userName')) {
                $table->string('userName')->nullable()->after('email_verified_at');
            }
            if (! Schema::hasColumn('drivers', 'status')) {
                // pending | active | suspended. Existing rows are approved drivers,
                // so default to 'active'; the registration flow sets 'pending'.
                $table->string('status', 16)->default('active')->index()->after('is_registered');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'email')) {
                // drop the unique index first (name follows Laravel's convention)
                try {
                    $table->dropUnique('drivers_email_unique');
                } catch (\Throwable $e) {
                    // index may not exist on some drivers — ignore
                }
                $table->dropColumn('email');
            }
            foreach (['email_verified_at', 'userName', 'status'] as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
