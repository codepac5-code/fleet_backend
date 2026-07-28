<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_safety_events')) {
            return;
        }

        Schema::create('driver_safety_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id')->index();
            $table->unsignedBigInteger('booking_id')->nullable()->index();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->string('kind', 16);
            $table->string('category', 32)->nullable();
            $table->string('status', 16)->default('open');
            $table->text('note')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->unsignedInteger('hold_ms')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['driver_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_safety_events');
    }
};
