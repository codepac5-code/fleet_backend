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
        Schema::create('commission_earnings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique(); 
            $table->unsignedBigInteger('driver_id'); 
            $table->unsignedBigInteger('office_id'); 
            $table->unsignedBigInteger('fleet_id'); 
            $table->double('total_fare'); 
            $table->double('office_commission')->nullable(); 
            $table->double('driver_commission')->nullable(); 
            $table->double('fleet_commission')->nullable(); 
            $table->dateTime('payment_date')->nullable(); 
            $table->enum('commission_status', ['pending', 'paid'])->default('pending'); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_earnings');
    }
};
