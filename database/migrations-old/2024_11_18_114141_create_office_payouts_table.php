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
        Schema::create('office_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('officeId')->nullable();
            $table->text('payment_method')->nullable();
            $table->text('description')->nullable();
            $table->double('amount')->nullable();
            $table->string('status')->default('unpaid')->nullable()->comment('pending , paid , unpaid');
            // $table->integer('bank_id')->nullable();
            $table->dateTime('paidDate')->nullable();
            $table->foreign('officeId')->references('id')->on('offices')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_payouts');
    }
};
