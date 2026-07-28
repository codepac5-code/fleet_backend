<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dispatch_jobs')) {
            return;
        }

        Schema::create('dispatch_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('office_id');
            $table->string('service_class', 32)->nullable();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('status', 16)->default('pending');
            $table->unsignedBigInteger('assigned_driver_id')->nullable();
            $table->unsignedInteger('wave')->default(0);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->index(['office_id', 'status']);
            $table->index('assigned_driver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_jobs');
    }
};
