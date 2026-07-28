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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("name_en");
            $table->text("image");
            $table->string('type')->comment('cash , fleetWallet' , 'syriatel' , 'mtn');
            $table->boolean("status")->default(true);
            // $table->foreignId('officeId')->references('id')->on('offices')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
