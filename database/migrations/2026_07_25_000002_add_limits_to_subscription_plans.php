<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends subscription plans for the office model: a monthly ride cap and the
 * overage prices charged when an office exceeds its plan (per extra ride and
 * per extra driver). All nullable — null = unlimited / no overage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('ride_limit')->nullable()->after('driver_limit');
            $table->unsignedInteger('extra_ride_minor')->nullable()->after('ride_limit');
            $table->unsignedInteger('extra_driver_minor')->nullable()->after('extra_ride_minor');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['ride_limit', 'extra_ride_minor', 'extra_driver_minor']);
        });
    }
};
