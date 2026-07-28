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
        Schema::create('public_user_app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable()->default(null);
            $table->string('key');
            $table->string('name');
            $table->json('ar_value')->nullable();
            $table->json('en_value')->nullable();
            $table->index(["key"], 'user_public_app_settings_key_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_user_app_settings');
    }
};
