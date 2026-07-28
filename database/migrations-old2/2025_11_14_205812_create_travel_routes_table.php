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
        Schema::create('travel_routes', function (Blueprint $table) {
           $table->id();
            $table->foreignId('sub_service_id')->constrained('sub_services')->cascadeOnDelete();
            $table->foreignId('departure_city_id')->constrained('cities');
            $table->foreignId('arrival_city_id')->constrained('cities');
            $table->unsignedBigInteger('officeId')->nullable();
            $table->foreign('officeId')->references('id')->on('offices')->onDelete('set null');
            // $table->string('departure_city');
            // $table->string('arrival_city');
            $table->decimal('trip_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_routes');
    }
};
