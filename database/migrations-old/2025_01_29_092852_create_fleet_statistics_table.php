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
        Schema::create('fleet_statistics', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('pending_amount', 15, 2)->default(0);
            $table->decimal('withdrawn_amount', 15, 2)->default(0);
            $table->decimal('available_amount', 15, 2)->default(0);
            $table->decimal('drivers_debt', 15, 2)->default(0); 
            $table->decimal('offices_debt', 15, 2)->default(0);
            $table->bigInteger('users_count')->default(0);
            $table->bigInteger('drivers_count')->default(0);
            $table->bigInteger('offices_count')->default(0);
            $table->bigInteger('services_count')->default(0);
            $table->bigInteger('subServices_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_statistics');
    }
};
