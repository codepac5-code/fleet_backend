<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // online/offline flag — renamed from the legacy `isConected`.
            if (Schema::hasColumn('drivers', 'isConected') && ! Schema::hasColumn('drivers', 'is_online')) {
                $table->renameColumn('isConected', 'is_online');
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            // Paused-but-available state + its reason (break / fuel / prayer …).
            if (! Schema::hasColumn('drivers', 'busy')) {
                $table->boolean('busy')->default(false)->after('is_online');
            }
            if (! Schema::hasColumn('drivers', 'busy_reason')) {
                $table->string('busy_reason', 40)->nullable()->after('busy');
            }
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'busy_reason')) {
                $table->dropColumn('busy_reason');
            }
            if (Schema::hasColumn('drivers', 'busy')) {
                $table->dropColumn('busy');
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'is_online') && ! Schema::hasColumn('drivers', 'isConected')) {
                $table->renameColumn('is_online', 'isConected');
            }
        });
    }
};
