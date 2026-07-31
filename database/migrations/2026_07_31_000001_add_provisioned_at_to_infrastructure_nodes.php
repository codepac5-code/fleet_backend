<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Global-only registry table; shards don't carry it.
        if (! Schema::hasTable('infrastructure_nodes')) {
            return;
        }

        if (! Schema::hasColumn('infrastructure_nodes', 'provisioned_at')) {
            Schema::table('infrastructure_nodes', function (Blueprint $table) {
                $table->timestamp('provisioned_at')->nullable()->after('is_active');
            });
        }

        // Reconcile existing countries: a shard whose database actually exists and
        // holds tables is already provisioned. One created but not yet provisioned
        // (its DB doesn't exist) stays NULL so the panel never activates it.
        foreach (DB::table('infrastructure_nodes')->where('type', 'country')->get() as $node) {
            if (! empty($node->provisioned_at) || empty($node->db_host) || empty($node->db_name)) {
                continue;
            }

            try {
                $probe = 'prov_backfill_probe';
                config(['database.connections.' . $probe => [
                    'driver'    => 'mysql',
                    'host'      => $node->db_host,
                    'port'      => (int) ($node->db_port ?: 3306),
                    'database'  => '',
                    'username'  => $node->db_user,
                    'password'  => $node->db_pass,
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'options'   => [\PDO::ATTR_TIMEOUT => 5],
                ]]);
                DB::purge($probe);

                $tables = DB::connection($probe)->select(
                    'select count(*) as c from information_schema.tables where table_schema = ?',
                    [$node->db_name]
                )[0]->c ?? 0;

                DB::purge($probe);

                if ((int) $tables > 0) {
                    DB::table('infrastructure_nodes')->where('id', $node->id)->update(['provisioned_at' => now()]);
                }
            } catch (\Throwable $e) {
                // Unreachable / DB missing → leave NULL (treated as not provisioned).
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('infrastructure_nodes') && Schema::hasColumn('infrastructure_nodes', 'provisioned_at')) {
            Schema::table('infrastructure_nodes', function (Blueprint $table) {
                $table->dropColumn('provisioned_at');
            });
        }
    }
};
