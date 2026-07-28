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
        Schema::create('coupon_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('couponId')->nullable();
            $table->unsignedBigInteger('serviceId')->nullable();
            $table->foreign('couponId')->references('id')->on('coupons')->onDelete('cascade');
            $table->foreign('serviceId')->references('id')->on('services')->onDelete('cascade');
            $table->unique(['couponId', 'serviceId']);
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_services');
    }
};
