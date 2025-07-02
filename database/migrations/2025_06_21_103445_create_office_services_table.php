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
        Schema::create('office_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serviceId')->references('id')->on('services')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('officeId')->nullable();
            $table->foreign('officeId')->references('id')->on('offices')->onDelete('cascade');
            $table->tinyInteger('status')->nullable()->default('1')->comment('1- Active , 0- InActive');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_services');
    }
};
