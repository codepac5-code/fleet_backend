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
        Schema::create('driver_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orderId')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreignId('officeId')->references('id')->on('offices')->onDelete('cascade');
            //$table->foreignId('driverId')->references('id')->on('drivers')->onDelete('cascade');
            $table->double('amount')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('unpaid')->nullable()->comment('pending , paid , unpaid');
            $table->dateTime('paidDate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_payouts');
    }
};
