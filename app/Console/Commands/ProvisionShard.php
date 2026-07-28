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
                ShardManager::activate($node);

                if ($this->isReferenceItself($node)) {
                    $this->info(sprintf('[%s] shard IS the reference database — skipped cloning.', $node->name ?? $node->id));
                } else {
                    $created = $this->cloneSchema();
                    $this->copyMigrationLedger();
                    $this->info(sprintf('[%s] schema cloned (%d tables created).', $node->name ?? $node->id, $created));
                }

                $this->applyNewerMigrations();

                if ($this->option('seed')) {
                    Artisan::call('db:seed', ['--database' => TenantConnection::NAME, '--force' => true], $this->output);
                }

                $this->info(sprintf('[%s] shard ready.', $node->name ?? $node->id));
            } catch (Throwable $e) {
                $failures++;
                $this->error(sprintf('[%s] failed: %s', $node->name ?? $node->id, $e->getMessage()));
            }
        }

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
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
