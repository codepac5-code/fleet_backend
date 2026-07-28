<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commission_snapshots')) {
            return;
        }

        Schema::create('commission_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('office_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->string('currency_code', 10);
            $table->string('pricing_style', 16);
            $table->bigInteger('fare_minor');
            $table->bigInteger('discount_minor')->default(0);
            $table->bigInteger('total_minor');
            $table->decimal('fleet_rate', 7, 4)->default(0);
            $table->decimal('office_rate', 7, 4)->default(0);
            $table->bigInteger('fleet_minor')->default(0);
            $table->bigInteger('office_minor')->default(0);
            $table->bigInteger('driver_minor')->default(0);
            $table->string('subscription_plan', 32)->nullable();
            $table->timestamps();

            $table->unique('booking_id');
            $table->index(['office_id', 'driver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_snapshots');
    }
};
