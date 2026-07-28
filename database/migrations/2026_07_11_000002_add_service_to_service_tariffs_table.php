<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('service_tariffs') || Schema::hasColumn('service_tariffs', 'service')) {
            return;
        }

        Schema::table('service_tariffs', function (Blueprint $table) {
            $table->string('service', 16)->nullable()->after('office_id')->index();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('service_tariffs') && Schema::hasColumn('service_tariffs', 'service')) {
            Schema::table('service_tariffs', function (Blueprint $table) {
                $table->dropColumn('service');
            });
        }
    }
};
