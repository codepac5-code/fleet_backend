<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_applications')) {
            return;
        }

        Schema::create('driver_applications', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('name')->nullable();
            $table->string('city')->nullable();
            $table->string('vehicle_type', 64)->nullable();
            $table->string('license_number', 64)->nullable();
            $table->unsignedBigInteger('office_id')->nullable()->index();
            $table->string('invite_code', 32)->nullable();
            $table->string('kind', 16)->default('apply');
            $table->string('status', 16)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_applications');
    }
};
