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
        Schema::create('service_routes', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('subServiceId')->references('id')->on('sub_services')->onDelete('cascade');
            // $table->string('departureCity', 100);
            // $table->string('arrivalCity', 100);
            // $table->decimal('price', 10, 2)->unsigned();
            // $table->char('currency', 3)->default('SAR');
            // $table->boolean('isActive')->default(true);
            // $table->index('serviceId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_routes');
    }
};
