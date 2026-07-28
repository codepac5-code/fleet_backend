<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_tariffs')) {
            return;
        }

        Schema::create('service_tariffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id');
            $table->string('service', 16)->nullable()->index();
            $table->string('service_class')->default('standard');
            $table->string('currency_code', 3);
            $table->string('pricing_style')->default('meter');
            $table->unsignedBigInteger('base_minor')->default(0);
            $table->unsignedBigInteger('per_km_minor')->default(0);
            $table->unsignedBigInteger('per_minute_minor')->default(0);
            $table->unsignedBigInteger('minimum_minor')->default(0);
            $table->unsignedBigInteger('fixed_minor')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['office_id', 'service', 'service_class']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_tariffs');
    }
};
