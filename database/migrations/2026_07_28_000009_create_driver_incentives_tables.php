<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Driver incentives: admin-defined "do N rides in a window, get X" rules and the
 * per-driver progress against them. Both PER SHARD — the reward is money in the
 * country's currency and each country runs its own programme.
 *
 * A rule is a row, not code, so operations can launch a weekend push without a
 * deploy. Progress is a row per (rule, driver, period) with the period being the
 * window it belongs to, which makes crediting idempotent by unique key.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_incentives')) {
            Schema::create('driver_incentives', function (Blueprint $table) {
                $table->id();
                $table->string('name_en', 120);
                $table->string('name_ar', 120);
                $table->string('window', 8)->default('week');   // day | week | month
                $table->unsignedSmallInteger('target_rides');
                $table->unsignedInteger('reward_minor');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['is_active', 'window']);
            });
        }

        if (! Schema::hasTable('driver_incentive_progress')) {
            Schema::create('driver_incentive_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('incentive_id')->index();
                $table->unsignedBigInteger('driver_id')->index();
                $table->string('period', 16);                   // 2026-07-28 | 2026-W31 | 2026-07
                $table->unsignedSmallInteger('rides')->default(0);
                $table->boolean('rewarded')->default(false);
                $table->unsignedInteger('reward_minor')->default(0);
                $table->string('currency_code', 3)->nullable();
                $table->timestamp('rewarded_at')->nullable();
                $table->timestamps();

                $table->unique(['incentive_id', 'driver_id', 'period']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_incentive_progress');
        Schema::dropIfExists('driver_incentives');
    }
};
