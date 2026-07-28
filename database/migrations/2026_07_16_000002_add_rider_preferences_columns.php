<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rider_profiles')) {
            return;
        }

        Schema::table('rider_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('rider_profiles', 'notification_prefs')) {
                $table->json('notification_prefs')->nullable()->after('locale');
            }

            if (! Schema::hasColumn('rider_profiles', 'privacy_prefs')) {
                $table->json('privacy_prefs')->nullable()->after('notification_prefs');
            }

            if (! Schema::hasColumn('rider_profiles', 'auto_share_safety')) {
                $table->boolean('auto_share_safety')->default(true)->after('privacy_prefs');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rider_profiles')) {
            return;
        }

        Schema::table('rider_profiles', function (Blueprint $table) {
            $table->dropColumn(['notification_prefs', 'privacy_prefs', 'auto_share_safety']);
        });
    }
};
