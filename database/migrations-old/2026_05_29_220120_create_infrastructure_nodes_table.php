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
        Schema::create('infrastructure_nodes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [ 'country', 'zone', 'shard' ]);
            $table->string('name');
            $table->foreignId('parent_id')->nullable();
            $table->string('country_code')->nullable();
            $table->string('city')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('radius_km', 10, 2)->nullable();
            $table->string('db_host')->nullable();
            $table->string('db_name')->nullable();
            $table->string('db_user')->nullable();
            $table->string('db_pass')->nullable();
            $table->integer('db_port')->nullable();
            $table->string('redis_host')->nullable();
            $table->integer('redis_db')->nullable();
            $table->string('redis_prefix')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infrastructure_nodes');
    }
};
