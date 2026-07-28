<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_applications', function (Blueprint $table) {
            // Optional license document uploaded during onboarding (public URL).
            $table->string('license_path')->nullable()->after('license_number');
        });
    }

    public function down(): void
    {
        Schema::table('driver_applications', function (Blueprint $table) {
            $table->dropColumn('license_path');
        });
    }
};
