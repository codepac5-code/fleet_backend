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
        Schema::create('sub_services', function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('openPrice', 10, 2);
            $table->decimal('kmPrice', 10, 2);
            $table->decimal('minutePrice', 10, 2);
            $table->foreignId('serviceId')->references('id')->on('services')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('name_en');
            $table->boolean('is_travel')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_services');
    }
};
