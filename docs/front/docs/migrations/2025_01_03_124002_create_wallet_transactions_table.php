<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id(); 
            // $table->unsignedBigInteger('from_wallet_id'); 
            // $table->unsignedBigInteger('to_wallet_id'); 
            $table->morphs('from');
            $table->morphs('to');
            $table->double('amount'); 
            $table->double('balance_before')->nullable(); 
            $table->double('balance_after')->nullable(); 
            $table->string('description');
            $table->string('description_en');
            $table->string('paymentName');
            $table->string('paymentName_en');
            // $table->unsignedBigInteger('related_id')->nullable();
            $table->morphs('source');
            $table->string('status', 50)->default('pending')->comment('pending , completed , failed'); 
            $table->string('transaction_type')->nullable()->comment('income, withdrawal, commission, due, etc');
            $table->string('transaction_reference', 100)->nullable();

            $table->index(['transaction_type']);

            // $table->string('source_type', 100)->nullable()->comment('Ride , Deposit '); 
            // $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
