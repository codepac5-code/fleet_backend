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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->references('id')->on('users')->onDelete('cascade');
            $table->string('subject');
            $table->longText('description');
            $table->string('mode')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('isOpen')->default(true);
            $table->string('status')->nullable()->default('0');
            $table->softDeletes();


            // $table->unsignedBigInteger('employee_id');
            // $table->string('email')->unique()->nullable();
            // $table->string('contact_number', 255)->nullable();
            // $table->string('status')->nullable()->default('0');

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
