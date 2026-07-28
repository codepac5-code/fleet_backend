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
        Schema::create('office_user_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('officeId');
            $table->unsignedBigInteger('userId');
            $table->integer('totalBookings')->default(0);
            $table->double('totalAmount', 15, 2)->default(0);
            $table->double('totalDistance', 10, 2)->default(0);
            $table->timestamp('lastBookingAt')->nullable();
            $table->float('averageRating')->default(0);
            $table->string('lastPaymentStatus', 20)->nullable();
            $table->timestamps();

            $table->foreign('officeId')->references('id')->on('offices')->onDelete('cascade');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['officeId', 'userId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_user_stats');
    }
};
