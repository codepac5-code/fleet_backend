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
        Schema::create('wallet_transaction_groups', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference', 100)->unique(); 
            $table->morphs('from'); 
            $table->morphs('to');   
            $table->double('amount'); 
            $table->double('balance_before')->nullable(); 
            $table->double('balance_after')->nullable(); 
            $table->string('description');
            $table->string('description_en');
            $table->string('paymentName');
            $table->string('paymentName_en');
            $table->morphs('source'); 
            $table->string('status', 50)->default('pending')->comment('pending , completed , failed'); 
            $table->string('transaction_type')->nullable()->comment('income, withdrawal, commission, due, etc');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transaction_groups');
    }
};
