<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dispatch_offers')) {
            return;
        }

        Schema::create('dispatch_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedInteger('wave')->default(0);
            $table->string('status', 16)->default('offered');
            $table->unsignedInteger('distance_m')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'driver_id'], 'dispatch_offers_booking_driver_unique');
            $table->index(['driver_id', 'status']);
            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_offers');
    }
};
