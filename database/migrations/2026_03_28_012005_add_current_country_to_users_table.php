<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('users', 'current_country')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('current_country', 5)->nullable()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'current_country')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('current_country');
        });
    }
};
