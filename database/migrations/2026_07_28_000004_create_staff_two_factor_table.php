<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TOTP enrolment for panel staff. Lives on the platform connection because an
 * admin belongs to no country — office/employee rows carry `country_code` since
 * their ids repeat across shards, so (guard, staff_id) alone is not unique.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staff_two_factor')) {
            return;
        }

        Schema::create('staff_two_factor', function (Blueprint $table) {
            $table->id();
            $table->string('guard', 16);
            $table->unsignedBigInteger('staff_id');
            $table->string('country_code', 2)->nullable();
            $table->text('secret');                      // encrypted
            $table->text('recovery_codes')->nullable();  // encrypted json of hashes
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['guard', 'staff_id', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_two_factor');
    }
};
