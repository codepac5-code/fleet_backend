<?php

namespace App\Console\Commands;

use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\InfrastructureNode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProvisionShard extends Command
{
    protected $signature = 'fleet:shard-provision {country? : ISO2 country code} {--id= : InfrastructureNode id} {--all : provision every active country shard} {--seed : run database seeders on the shard}';

    protected $description = 'Provision a country shard: clone the reference schema (the global DB) into the shard, then apply any newer migrations. Idempotent.';

    private const REFERENCE = 'global';

    public function handle(): int
    {
        $nodes = $this->resolveNodes();

        if ($nodes === null) {
            return self::FAILURE;
        }

        if ($nodes->isEmpty()) {
            $this->error('No matching active country shard found.');

            return self::FAILURE;
        }

        $failures = 0;

        foreach ($nodes as $node) {
            if (empty($node->db_name) || empty($node->db_host)) {
                $this->warn(sprintf('[%s] missing DB credentials — skipped.', $node->name ?? $node->id));
                continue;
            }

            $this->line(sprintf('Provisioning shard %s (%s) → %s@%s', $node->name ?? $node->id, $node->country_code, $node->db_name, $node->db_host));

            try {
                $this->ensureDatabase($node);
                ShardManager::activate($node);

                if ($this->isReferenceItself($node)) {
                    $this->info(sprintf('[%s] shard IS the reference database — skipped cloning.', $node->name ?? $node->id));
                } else {
                    $created = $this->cloneSchema();
                    $this->copyMigrationLedger();
                    $added = $this->syncMissingColumns();
                    $this->info(sprintf('[%s] schema cloned (%d tables created, %d columns added).', $node->name ?? $node->id, $created, $added));
                }

                $this->applyNewerMigrations();

                if ($this->option('seed')) {
                    Artisan::call('db:seed', ['--database' => TenantConnection::NAME, '--force' => true], $this->output);
                }

                // Make the country fully supported the moment its shard is ready:
                // register its currency (global) and seed its provinces (shard),
                // both from the bundled CountryProfiles. Idempotent — safe to
                // re-run on an existing shard.
                $support = app(\App\Http\Core\GeoServices\CountrySupportService::class);
                $support->registerCurrency($node);
                $seededProvinces = $support->seedProvinces($node);
                if ($seededProvinces > 0) {
                    $this->info(sprintf('[%s] seeded %d provinces.', $node->name ?? $node->id, $seededProvinces));
                }

                // Mark provisioned so the panel is allowed to activate this shard;
                // until now it was created but its DB may not have existed, and
                // activating it would crash every tenant query with "Unknown
                // database". Written on the GLOBAL registry, not the shard.
                InfrastructureNode::query()->whereKey($node->id)->update(['provisioned_at' => now()]);

                $this->info(sprintf('[%s] shard ready.', $node->name ?? $node->id));
            } catch (Throwable $e) {
                $failures++;
                $this->error(sprintf('[%s] failed: %s', $node->name ?? $node->id, $e->getMessage()));
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function ensureDatabase(InfrastructureNode $node): void
    {
        $probe = 'shard_provision_probe';

        config(['database.connections.' . $probe => [
            'driver'    => 'mysql',
            'host'      => $node->db_host,
            'port'      => (int) ($node->db_port ?: 3306),
            'database'  => '',
            'username'  => $node->db_user,
            'password'  => $node->db_pass,
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        DB::purge($probe);

        $db = str_replace('`', '``', (string) $node->db_name);
        DB::connection($probe)->statement(
            "CREATE DATABASE IF NOT EXISTS `{$db}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        DB::purge($probe);
    }

    private function isReferenceItself(InfrastructureNode $node): bool
    {
        $ref = config('database.connections.' . self::REFERENCE);

        return (string) $node->db_name === (string) ($ref['database'] ?? null)
            && (string) $node->db_host === (string) ($ref['host'] ?? null)
            && (int) ($node->db_port ?: 3306) === (int) ($ref['port'] ?? 3306);
    }

    private function cloneSchema(): int
    {
        $reference = DB::connection(self::REFERENCE);
        $shard = DB::connection(TenantConnection::NAME);
        $refDb = $reference->getDatabaseName();

        $tables = $reference->select(
            'select table_name as name from information_schema.tables where table_schema = ? and table_type = ? order by table_name',
            [$refDb, 'BASE TABLE']
        );

        $created = 0;
        $shard->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                if ($shard->getSchemaBuilder()->hasTable($table->name)) {
                    continue;
                }

                $ddl = $reference->select('SHOW CREATE TABLE `' . $table->name . '`');
                $create = $ddl[0]->{'Create Table'} ?? null;

                if ($create) {
                    $shard->statement($create);
                    $created++;
                }
            }
        } finally {
            $shard->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        return $created;
    }

    /**
     * Adds columns the reference has and the shard does not.
     *
     * Cloning only creates MISSING TABLES, and copying the migration ledger then
     * marks every reference migration as applied — so a migration that ALTERs an
     * existing table (adds a column) was silently skipped on shards provisioned
     * earlier, and only showed up as a "no such column" error at runtime. This
     * closes that gap deterministically. Additive only: it never drops, renames
     * or retypes a column.
     */
    private function syncMissingColumns(): int
    {
        $reference = DB::connection(self::REFERENCE);
        $shard = DB::connection(TenantConnection::NAME);
        $refDb = $reference->getDatabaseName();
        $shardDb = $shard->getDatabaseName();

        $columns = $reference->select(
            'select table_name as tbl, column_name as col, column_type as type, is_nullable as nullable,
                    column_default as dflt, extra, ordinal_position as pos
             from information_schema.columns
             where table_schema = ? order by table_name, ordinal_position',
            [$refDb]
        );

        $existing = [];
        foreach ($shard->select('select table_name as tbl, column_name as col from information_schema.columns where table_schema = ?', [$shardDb]) as $row) {
            $existing[$row->tbl][$row->col] = true;
        }

        $byTable = [];
        foreach ($columns as $column) {
            $byTable[$column->tbl][] = $column;
        }

        $added = 0;

        foreach ($byTable as $table => $tableColumns) {
            if (! isset($existing[$table])) {
                continue;
            }

            $previous = null;

            foreach ($tableColumns as $column) {
                if (isset($existing[$table][$column->col])) {
                    $previous = $column->col;
                    continue;
                }

                $sql = sprintf(
                    'ALTER TABLE `%s` ADD COLUMN `%s` %s %s %s %s',
                    $table,
                    $column->col,
                    $column->type,
                    $column->nullable === 'YES' ? 'NULL' : 'NOT NULL',
                    $this->defaultClause($column),
                    $previous !== null ? 'AFTER `' . $previous . '`' : 'FIRST'
                );

                try {
                    $shard->statement($sql);
                    $added++;
                    $this->line(sprintf('  + %s.%s', $table, $column->col));
                } catch (Throwable $e) {
                    $this->warn(sprintf('  ! %s.%s could not be added: %s', $table, $column->col, $e->getMessage()));
                }

                $previous = $column->col;
            }
        }

        return $added;
    }

    private function defaultClause(object $column): string
    {
        // information_schema reports "no default" as either a real null or the
        // literal string NULL depending on the server — both mean the same thing,
        // and quoting the literal produces the invalid `DEFAULT 'NULL'`.
        $default = $column->dflt;

        if ($default === null || strtoupper((string) $default) === 'NULL') {
            return $column->nullable === 'YES' ? 'DEFAULT NULL' : '';
        }

        $isExpression = in_array(strtoupper((string) $default), ['CURRENT_TIMESTAMP', 'NOW()'], true);

        return 'DEFAULT ' . ($isExpression ? $default : "'" . addslashes((string) $default) . "'");
    }

    private function copyMigrationLedger(): void
    {
        $reference = DB::connection(self::REFERENCE);
        $shard = DB::connection(TenantConnection::NAME);

        if (! $shard->getSchemaBuilder()->hasTable('migrations')) {
            return;
        }

        $applied = $shard->table('migrations')->pluck('migration')->all();

        $pending = $reference->table('migrations')
            ->when($applied, fn ($q) => $q->whereNotIn('migration', $applied))
            ->get(['migration', 'batch']);

        foreach ($pending as $row) {
            $shard->table('migrations')->insert(['migration' => $row->migration, 'batch' => $row->batch]);
        }
    }

    private function applyNewerMigrations(): void
    {
        $shard = DB::connection(TenantConnection::NAME);

        $shard->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            Artisan::call('migrate', ['--database' => TenantConnection::NAME, '--force' => true], $this->output);
        } finally {
            $shard->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function resolveNodes()
    {
        $query = InfrastructureNode::query()->where('type', 'country')->where('is_active', true);

        if ($this->option('all')) {
            return $query->get();
        }

        if ($this->option('id')) {
            return InfrastructureNode::query()->where('id', (int) $this->option('id'))->get();
        }

        if ($this->argument('country')) {
            return $query->whereRaw('LOWER(country_code) = ?', [strtolower($this->argument('country'))])->get();
        }

        $this->error('Provide a country code, --id=<node>, or --all.');

        return null;
    }
}
