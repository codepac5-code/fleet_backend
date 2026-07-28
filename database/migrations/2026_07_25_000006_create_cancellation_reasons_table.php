<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Managed cancellation-reason catalog (per-country shard). Replaces the free-text
 * `cancel_reason` with a curated, localized, audience-scoped picklist that both
 * apps read. Per-shard so each country curates its own list.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cancellation_reasons')) {
            return;
        }

        Schema::create('cancellation_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();          // stable slug sent as the reason
            $table->string('label_en', 120);
            $table->string('label_ar', 120);
            $table->string('audience', 8)->default('both'); // rider | driver | both
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['audience', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_reasons');
    }
};
