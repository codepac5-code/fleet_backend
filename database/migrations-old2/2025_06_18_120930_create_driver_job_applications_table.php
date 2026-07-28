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
        Schema::create('driver_job_applications', function (Blueprint $table) {
            $table->id();

            //driver info
            $table->string('name');
            $table->string('phoneNumber')->unique();
            $table->string('password'); 
            // $table->foreignId('officeId')->references('id')->on('offices')->cascadeOnDelete();
    
            //car info
            // $table->foreignId('brandId')->references('id')->on('vehicle_brands')->cascadeOnDelete();
            $table->string('brand');
            $table->string('model');
            $table->string('year');
            $table->string('color');
            $table->string('plateNumber');

            // personal photo and documents
            $table->string('profileImage')->nullable();
            $table->string('idFrontImage')->nullable();
            $table->string('idBackImage')->nullable();
            $table->string('licenseFrontImage')->nullable();
            $table->string('licenseBackImage')->nullable();
            $table->string('mechanicalImage')->nullable();
            
            // car photos
            $table->string('frontCarImage')->nullable();
            $table->string('backCarImage')->nullable();
            $table->string('rightCarImage')->nullable();
            $table->string('leftCarImage')->nullable();
            $table->string('insideCarImage')->nullable();
            $table->string('frontSeatsImage')->nullable();
            $table->string('backSeatsImage')->nullable();
            
            // $table->string('optionalVideo')->nullable();
            
    
            // order status 
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_job_applications');
    }
};
