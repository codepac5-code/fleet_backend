<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The rider-support tables (lost_items, complaints) live on the GLOBAL connection
 * but reference per-shard offices/bookings whose ids repeat across countries. A
 * `country_code` discriminator makes every read country-isolatable so an office
 * can never see another country's reports. Stamped at creation by the models.
 */
return new class extends Migration
{
    private array $tables = ['lost_items', 'complaints'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'country_code')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('country_code', 2)->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'country_code')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('country_code');
                });
            }
        }
    }
};
