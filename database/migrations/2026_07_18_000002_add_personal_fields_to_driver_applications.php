<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('driver_applications', function (Blueprint $table) {
            // Personal info collected at registration, mirroring the `drivers`
            // table so the office can create the driver account on approval.
            $table->string('first_name', 30)->nullable()->after('name');
            $table->string('last_name', 30)->nullable()->after('first_name');
            $table->string('gender', 10)->nullable()->after('last_name');
            $table->string('country')->nullable()->after('city');
            $table->string('region')->nullable()->after('country');
            $table->text('address')->nullable()->after('region');
            $table->boolean('car_owner')->default(false)->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('driver_applications', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'gender', 'country', 'region', 'address', 'car_owner']);
        });
    }
};
