<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'assignedAt')) {
                $table->dateTime('assignedAt')->nullable()->after('driverId');
            }
            if (! Schema::hasColumn('bookings', 'cancelledAt')) {
                $table->dateTime('cancelledAt')->nullable()->after('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['assignedAt', 'cancelledAt'] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
