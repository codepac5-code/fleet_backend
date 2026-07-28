<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_presence')) {
            return;
        }

        Schema::create('driver_presence', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->primary();
            $table->unsignedBigInteger('office_id')->nullable();
            $table->string('status', 16)->default('offline');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('geohash', 12)->nullable();
            $table->timestamp('heartbeat_at')->nullable();
            $table->timestamps();

            $table->index(['office_id', 'status']);
            $table->index(['lat', 'lng']);
            $table->index('geohash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_presence');
    }
};
