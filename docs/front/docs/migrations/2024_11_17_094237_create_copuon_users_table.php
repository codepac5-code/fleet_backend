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
        Schema::create('coupon_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('couponId')->references('id')->on('coupons')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('userId')->index();
            //$table->foreignId('userId');//->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('count')->default(0);
            $table->unsignedBigInteger('userId')->index();
            $table->unique(['couponId', 'userId','couponId']);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('copon_users');
    }
};
