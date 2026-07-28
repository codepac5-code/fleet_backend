<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subscription_plans')) {
            return;
        }

        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('name', 64);
            $table->bigInteger('price_minor')->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->decimal('fleet_commission_rate', 7, 4)->nullable();
            $table->unsignedInteger('driver_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
