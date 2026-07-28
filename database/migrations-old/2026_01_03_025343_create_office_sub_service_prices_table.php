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
        Schema::create('office_sub_service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('sub_service_id')->constrained('sub_services')->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('openPrice', 10, 2);
            $table->decimal('kmPrice', 10, 2);
            $table->decimal('minutePrice', 10, 2);
            $table->timestamps();
            $table->unique(['office_id', 'sub_service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_sub_service_prices');
    }
};
