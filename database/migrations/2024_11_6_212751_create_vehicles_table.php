<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('officeId')->nullable();
            $table->foreign('officeId')->references('id')->on('offices')->onDelete('cascade');
            $table->string('vehicleBrand');
            $table->text('plate');
            $table->string('modelYear');
            $table->string('licenseNumber')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('lastDriver')->nullable();
            $table->foreign('lastDriver')->references('id')->on('drivers')->onDelete('set null');
            $table->string('color');
            $table->string('city');
            $table->boolean('fleet_car')->default(false);
            $table->unsignedBigInteger('driverId')->nullable();
            $table->foreign('driverId')->references('id')->on('drivers')->onDelete('cascade');
            // $table->foreignId('subServiceId')->references('id')->on('sub_services')->onDelete('cascade');
            $table->text('description')->nullable(); 
            $table->integer('seatsCount')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
