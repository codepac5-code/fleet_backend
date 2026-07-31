<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Managed lookup catalogs behind the free-text `vehicles.model` / `vehicles.color`
 * fields. Reference lists (a Corolla is a Corolla everywhere), so they sit beside
 * `vehicle_brands` on the platform connection rather than per shard. The vehicle
 * columns stay free text — the catalog only feeds the picker, so legacy rows and
 * unlisted models keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehicle_models')) {
            Schema::create('vehicle_models', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('brand_id')->nullable()->index();
                $table->string('name');
                $table->string('name_en');
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('vehicle_colors')) {
            Schema::create('vehicle_colors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_en');
                $table->string('hex', 7)->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
        Schema::dropIfExists('vehicle_colors');
    }
};
