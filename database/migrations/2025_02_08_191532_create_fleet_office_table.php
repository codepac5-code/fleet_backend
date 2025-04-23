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
        Schema::create('fleet_office', function (Blueprint $table) {
            $table->id();
            // $table->enum('commission_type', ['percentage', 'fixed'])->default('percentage')->comment('percentage , fixed');
            $table->decimal('fleet_commission_value_with_driver', 10, 2)->default(0);
            $table->decimal('fleet_commission_value_with_office', 10, 2)->default(0);
            $table->decimal('office_commission_value', 10, 2)->default(0);
            $table->decimal('driver_commission_value', 10, 2)->default(0);
            $table->double('walletBalance')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_office');
    }
};
