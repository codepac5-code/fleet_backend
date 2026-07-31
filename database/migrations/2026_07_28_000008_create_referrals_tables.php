<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rider referrals.
 *
 * `referrals` links two GLOBAL user rows, so it lives on the platform connection
 * with a `country_code` stamped when the reward is paid — the money itself moves
 * in that country's ledger. `referral_settings` is PER SHARD because the reward
 * is an amount in the country's own currency, and each country runs its own
 * programme (or none).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('referrer_user_id')->index();
                $table->unsignedBigInteger('invitee_user_id')->unique(); // one referrer per rider, ever
                $table->string('code', 32);
                $table->string('status', 12)->default('pending');        // pending | rewarded
                $table->unsignedBigInteger('qualifying_booking_id')->nullable();
                $table->string('country_code', 2)->nullable()->index();
                $table->unsignedInteger('referrer_reward_minor')->default(0);
                $table->unsignedInteger('invitee_reward_minor')->default(0);
                $table->string('currency_code', 3)->nullable();
                $table->timestamp('rewarded_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'country_code']);
            });
        }

        if (! Schema::hasTable('referral_settings')) {
            Schema::create('referral_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_active')->default(false);
                $table->unsignedInteger('referrer_reward_minor')->default(0);
                $table->unsignedInteger('invitee_reward_minor')->default(0);
                $table->unsignedSmallInteger('qualifying_rides')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('referral_settings');
    }
};
