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
        Schema::create('help_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_en');
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('isActive')->default(true);
            $table->integer('priority')->default(0);
            $table->string('category')->nullable();
            $table->enum('target_user', ['user', 'driver', 'web'])->default('user');
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('created_by')->references('id')->on('admins')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_suggestions');
    }
};
