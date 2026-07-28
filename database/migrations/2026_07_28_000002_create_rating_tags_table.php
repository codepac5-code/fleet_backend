<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Managed rating-tag catalog (per-country shard). `ride_ratings.tags` already
 * accepted a free-text array that both apps filled from hardcoded lists; this is
 * the curated, localized picklist behind it. Tags are star-ranged so the app can
 * show "what went wrong" chips on a 1-2 star rating and praise chips on a 5.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rating_tags')) {
            return;
        }

        Schema::create('rating_tags', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();           // stable slug stored in ride_ratings.tags
            $table->string('label_en', 120);
            $table->string('label_ar', 120);
            $table->string('audience', 8)->default('both'); // rider (rates driver) | driver (rates rider) | both
            $table->unsignedTinyInteger('stars_min')->default(1);
            $table->unsignedTinyInteger('stars_max')->default(5);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['audience', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_tags');
    }
};
