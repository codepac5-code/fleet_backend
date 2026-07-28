<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ride_ratings')) {
            return;
        }

        Schema::create('ride_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->string('rater_type');
            $table->unsignedBigInteger('rater_id');
            $table->string('ratee_type');
            $table->unsignedBigInteger('ratee_id');
            $table->unsignedTinyInteger('stars');
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unique(['booking_id', 'rater_type', 'ratee_type']);
            $table->index(['ratee_type', 'ratee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ride_ratings');
    }
};
