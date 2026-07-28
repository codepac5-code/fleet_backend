<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_sub_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicleId')->references('id')->on('vehicles')->onDelete('cascade')
            ->index();
            $table->foreignId('subServiceId')->references('id')->on('sub_services')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_sub_services');
    }
};
