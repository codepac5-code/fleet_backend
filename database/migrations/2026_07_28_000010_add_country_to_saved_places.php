<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `saved_places` is global and holds BOTH rider and driver places. A rider is a
 * global account, so their home/work travelling between countries is correct —
 * but a DRIVER belongs to one country's database, and driver ids repeat across
 * them, so driver #26 in Syria was reading driver #26's places in Qatar. The
 * country stamp is what separates the driver rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_places') && ! Schema::hasColumn('saved_places', 'country_code')) {
            Schema::table('saved_places', function (Blueprint $table) {
                $table->string('country_code', 2)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('saved_places') && Schema::hasColumn('saved_places', 'country_code')) {
            Schema::table('saved_places', function (Blueprint $table) {
                $table->dropColumn('country_code');
            });
        }
    }
};
