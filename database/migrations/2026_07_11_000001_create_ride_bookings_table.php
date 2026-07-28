<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ride_bookings')) {
            return;
        }

        Schema::create('ride_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('office_id')->index();
            $table->string('service', 16);
            $table->string('service_class', 32);
            $table->unsignedBigInteger('sub_service_id')->nullable()->index();
            $table->string('pricing_style', 16);
            $table->string('status', 16)->default('matching')->index();
            $table->decimal('pickup_lat', 10, 7);
            $table->decimal('pickup_lng', 10, 7);
            $table->string('pickup_note')->nullable();
            $table->decimal('dropoff_lat', 10, 7);
            $table->decimal('dropoff_lng', 10, 7);
            $table->unsignedInteger('distance_m')->default(0);
            $table->unsignedInteger('duration_s')->default(0);
            // Live server-side meter (accumulated from GPS pings during ON_TRIP).
            $table->unsignedInteger('meter_distance_m')->default(0);
            $table->decimal('meter_last_lat', 10, 7)->nullable();
            $table->decimal('meter_last_lng', 10, 7)->nullable();
            $table->timestamp('trip_started_at')->nullable();
            $table->string('currency_code', 3);
            $table->integer('fare_minor')->default(0);
            $table->integer('discount_minor')->default(0);
            $table->integer('total_minor')->default(0);
            $table->integer('held_minor')->default(0);
            $table->string('payment_method', 16)->default('wallet');
            $table->string('promo_code')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_bookings');
    }
};
