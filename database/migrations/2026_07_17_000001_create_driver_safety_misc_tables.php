<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Driver-owned tables for the DriverX "safety + misc" surface. Kept separate
 * from the rider-owned `safety_contacts` (global, user_id) per the ownership
 * decision — the driver side never shares rows with a rider. All live on the
 * tenant shard (like `driver_safety_events`), guarded by hasTable so a re-run
 * on an already-migrated shard is a no-op.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_safety_contacts')) {
            Schema::create('driver_safety_contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id')->index();
                $table->string('name', 120);
                $table->string('phone', 32);
                $table->string('relation', 40)->nullable();
                $table->boolean('is_primary')->default(false);
                $table->boolean('auto_share')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('driver_status_links')) {
            Schema::create('driver_status_links', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id')->index();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->string('token', 40)->unique();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('driver_app_settings')) {
            Schema::create('driver_app_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id')->unique();
                $table->string('locale', 8)->nullable();
                $table->boolean('auto_payout')->nullable();
                $table->boolean('accept_cash')->nullable();
                $table->string('payout_bank_id', 64)->nullable();
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('driver_deletion_requests')) {
            Schema::create('driver_deletion_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id')->index();
                $table->string('status', 24)->default('pending');
                $table->text('reason')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_deletion_requests');
        Schema::dropIfExists('driver_app_settings');
        Schema::dropIfExists('driver_status_links');
        Schema::dropIfExists('driver_safety_contacts');
    }
};
