<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create the `drivers_issues` table backing App\Models\DriversIssue — the
 * driver-raised support ticket / trip issue (Report trip issue + office/FleetOS
 * support screens). Replies live in the polymorphic `replies` table
 * (App\Models\DriverRepliesIssue, keyed by issueId).
 *
 * The DriversIssue model existed with no migration (see the drift appendix in
 * docs/BACKEND_HANDOFF_NOTES.md). Columns mirror the model's $fillable exactly.
 *
 * Idempotent: guarded by hasTable so it is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('drivers_issues')) {
            return;
        }

        Schema::create('drivers_issues', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('description');
            $table->string('photo')->nullable();               // uploaded image url
            $table->unsignedBigInteger('driverId')->index();   // fk drivers.id
            $table->boolean('isClosed')->default(false)->index();
            $table->timestamp('closedAt')->nullable();
            $table->timestamps();

            $table->foreign('driverId')
                ->references('id')->on('drivers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers_issues');
    }
};
