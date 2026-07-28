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
        Schema::create('office_driver_custom_commissions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('driverId')
                  ->constrained('drivers')
                  ->onDelete('cascade');
        
            $table->foreignId('officeId')
                  ->constrained('offices')
                  ->onDelete('cascade');
        
            $table->decimal('driverCommission', 10, 2)->default(0);
            $table->decimal('officeCommission', 10, 2)->default(0);


            $table->unique(['driverId', 'officeId']);   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_driver_custom_commissions');
    }
};
