<?php

namespace App\Http\Core\GeoServices;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\InfrastructureNode;
use Illuminate\Support\Facades\DB;

class ShardAggregator
{
    public const SCOPE = 'all';

    private static ?string $builtSignature = null;

    public static function isActive(): bool
    {
        return app()->bound('shard_all') && app('shard_all') === true;
    }

    public static function aggregateDb(): string
    {
        return (config('database.connections.global.database') ?: 'fleet') . '_aggregate';
    }

    /**
     * Distinct physical shard databases (deduped by host+port+db) that live on
     * the same server as the global connection — the only ones a cross-database
     * view can span.
     *
     * @return InfrastructureNode[]
     */
    public static function physicalShards(): array
    {
        $globalHost = (string) config('database.connections.global.host');
        $globalPort = (int) config('database.connections.global.port');

        $nodes = InfrastructureNode::query()
            ->where('type', 'country')
            ->where('is_active', true)
            ->whereNotNull('db_name')
            ->orderBy('id')
            ->get();

        $seen = [];
        $out = [];

        foreach ($nodes as $node) {
            if ((string) $node->db_host !== $globalHost || (int) ($node->db_port ?: 3306) !== $globalPort) {
                continue;
            }

            $key = strtolower($node->db_name);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $out[] = $node;
        }

        return $out;
    }

    public static function activate(): void
    {
        $shards = self::physicalShards();

        if (empty($shards)) {
            return;
        }

        $agg = self::aggregateDb();
        self::build($shards, $agg);

        $ref = config('database.connections.global');
        $prefix = 'database.connections.' . TenantConnection::NAME;
        config([
            $prefix . '.host'     => $ref['host'],
            $prefix . '.port'     => $ref['port'],
            $prefix . '.database' => $agg,
            $prefix . '.username' => $ref['username'],
            $prefix . '.password' => $ref['password'],
        ]);

        DB::purge(TenantConnection::NAME);
        DB::reconnect(TenantConnection::NAME);

        ShardContext::set(self::syntheticNode($shards));
        app()->instance('shard_all', true);
    }

    /**
     * Resolve (and lazily configure) a dedicated connection pointing at the
     * physical database of a given shard node — used to route writes on an
     * aggregate-loaded record back to its origin.
     */
    public static function shardConnection(int $nodeId): string
    {
        $name = 'shard_' . $nodeId;

        if (! config('database.connections.' . $name)) {
            $node = InfrastructureNode::query()->find($nodeId);

            if (! $node) {
                return TenantConnection::NAME;
            }

            config(['database.connections.' . $name => [
                'driver'    => 'mysql',
                'host'      => $node->db_host,
                'port'      => (int) ($node->db_port ?: 3306),
                'database'  => $node->db_name,
                'username'  => $node->db_user,
                'password'  => $node->db_pass ?? '',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);
        }

        return $name;
    }

    private static function build(array $shards, string $agg): void
    {
        $signature = md5(implode('|', array_map(fn ($n) => $n->id . ':' . $n->db_name . ':' . $n->country_code, $shards)));

        // Per-process guard: once built this request, never touch the DB again.
        if (self::$builtSignature === $signature) {
            return;
        }

        $ref = DB::connection('global');
        $ref->statement('CREATE DATABASE IF NOT EXISTS `' . $agg . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $baseDb = $shards[0]->db_name;

        // Cheap DB check: if the aggregate DB already holds one view per base
        // table for this exact shard set, the views are current — skip the
        // rebuild. No dependency on any cache backend (which may be down).
        $baseCount = (int) ($ref->select(
            'select count(*) as c from information_schema.tables where table_schema = ? and table_type = ?',
            [$baseDb, 'BASE TABLE']
        )[0]->c ?? 0);

        $viewCount = (int) ($ref->select(
            'select count(*) as c from information_schema.views where table_schema = ?',
            [$agg]
        )[0]->c ?? 0);

        if ($baseCount > 0 && $viewCount >= $baseCount && self::signatureMatches($ref, $agg, $signature)) {
            self::$builtSignature = $signature;

            return;
        }

        $tables = $ref->select(
            'select table_name as name from information_schema.tables where table_schema = ? and table_type = ?',
            [$baseDb, 'BASE TABLE']
        );

        foreach ($tables as $table) {
            $name = $table->name;

            $branches = [];
            foreach ($shards as $node) {
                $branches[] = sprintf(
                    "SELECT `t`.*, '%s' AS `_country`, %d AS `_shard` FROM `%s`.`%s` `t`",
                    addslashes((string) $node->country_code),
                    (int) $node->id,
                    $node->db_name,
                    $name
                );
            }

            try {
                $ref->statement('CREATE OR REPLACE VIEW `' . $agg . '`.`' . $name . '` AS ' . implode(' UNION ALL ', $branches));
            } catch (\Throwable $e) {
                // Tables whose columns diverge across shards are skipped; they
                // fall back to whatever connection their model pins to.
            }
        }

        self::writeSignature($ref, $agg, $signature);
        self::$builtSignature = $signature;
    }

    private static function signatureMatches($ref, string $agg, string $signature): bool
    {
        try {
            $row = $ref->select('select `sig` from `' . $agg . '`.`_agg_meta` limit 1');

            return isset($row[0]) && (string) $row[0]->sig === $signature;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function writeSignature($ref, string $agg, string $signature): void
    {
        try {
            $ref->statement('CREATE TABLE IF NOT EXISTS `' . $agg . '`.`_agg_meta` (`sig` varchar(64) not null)');
            $ref->statement('DELETE FROM `' . $agg . '`.`_agg_meta`');
            $ref->insert('insert into `' . $agg . '`.`_agg_meta` (`sig`) values (?)', [$signature]);
        } catch (\Throwable $e) {
        }
    }

    private static function syntheticNode(array $shards): InfrastructureNode
    {
        $node = new InfrastructureNode();
        $node->setAttribute('id', 0);
        $node->setAttribute('type', 'country');
        $node->setAttribute('name', 'All countries');
        $node->setAttribute('country_code', 'ALL');
        $node->setAttribute('currency_code', $shards[0]->currency_code ?? ShardManager::DEFAULT_CURRENCY);

        return $node;
    }

    public static function rebuild(): void
    {
        self::$builtSignature = null;

        try {
            DB::connection('global')->statement('DROP TABLE IF EXISTS `' . self::aggregateDb() . '`.`_agg_meta`');
        } catch (\Throwable $e) {
        }
    }
}
