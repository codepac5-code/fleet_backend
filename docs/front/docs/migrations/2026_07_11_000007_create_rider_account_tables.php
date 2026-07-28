<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rider_profiles')) {
            Schema::create('rider_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('email')->nullable();
                $table->string('locale', 5)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('safety_contacts')) {
            Schema::create('safety_contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('name');
                $table->string('phone');
                $table->boolean('auto_share')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('rider_payment_methods')) {
            Schema::create('rider_payment_methods', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('type', 16)->default('card');
                $table->string('brand', 24)->nullable();
                $table->string('last4', 4)->nullable();
                $table->string('exp', 7)->nullable();
                $table->string('gateway_token')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_payment_methods');
        Schema::dropIfExists('safety_contacts');
        Schema::dropIfExists('rider_profiles');
    }
};
