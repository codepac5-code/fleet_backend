<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `displayName` (the office's contact person) is in the Office model's fillable
 * and read by the panel sidebar, but the column was never created — so every
 * write that included it failed, which is why office SELF-REGISTRATION
 * (POST /office/register) crashed on insert. Adding the missing column instead
 * of dropping the field, since the panel already displays it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('offices') && ! Schema::hasColumn('offices', 'displayName')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->string('displayName')->nullable()->after('officeName');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('offices') && Schema::hasColumn('offices', 'displayName')) {
            Schema::table('offices', function (Blueprint $table) {
                $table->dropColumn('displayName');
            });
        }
    }
};
