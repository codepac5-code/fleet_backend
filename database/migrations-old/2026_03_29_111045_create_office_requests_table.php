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
        Schema::create('office_requests', function (Blueprint $table) {
            $table->id();

            $table->string('office_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone');
            $table->string('city');
            $table->string('country');
            $table->string('website')->nullable();

            $table->string('business_category');
            $table->integer('fleet_size');
            $table->string('service_type');
            $table->string('current_tools')->nullable();
            $table->string('coverage')->nullable();

            $table->string('license_status');
            $table->string('timeline');
            $table->text('notes')->nullable();

            $table->enum('status', ['new', 'reviewed'])->default('new');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_requests');
    }
};
