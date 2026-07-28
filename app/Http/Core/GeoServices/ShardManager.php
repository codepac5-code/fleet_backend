<?php

namespace App\Http\Core\GeoServices;

use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\InfrastructureNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShardManager
{
    public const DEFAULT_CURRENCY = 'USD';

    public static function activate(InfrastructureNode $node): void
    {
        ShardContext::set($node);

        $prefix = 'database.connections.' . TenantConnection::NAME;
        config([
            $prefix . '.host'     => $node->db_host,
            $prefix . '.port'     => $node->db_port,
            $prefix . '.database' => $node->db_name,
            $prefix . '.username' => $node->db_user,
            $prefix . '.password' => $node->db_pass,
        ]);

        DB::purge(TenantConnection::NAME);
        DB::reconnect(TenantConnection::NAME);

        app()->instance('region', strtolower((string) ($node->country_code ?? '')));
        app()->instance('shard_key', self::shardKeyFor($node));
        app()->instance('shard_currency', self::nodeCurrency($node));
    }

    /**
     * The namespace for realtime rooms and the Redis geo store.
     *
     * Deliberately the DATABASE, not the country. Those namespaces exist to stop
     * two entities that share a numeric id from colliding — and ids are minted
     * per database. SA and QA both live in `fleet`, so keying by country split
     * one id space into `qa.*` and `sa.*`: a booking created under SA searched
     * `fleet:geo:sa` and matched NONE of the drivers sitting in `fleet:geo:qa`,
     * metres away in the very same database. Two databases can never share an
     * id, so the database is exactly the right granularity.
     */
    public static function shardKeyFor(InfrastructureNode $node): string
    {
        return strtolower((string) ($node->db_name ?: ($node->country_code ?? '')));
    }

    /** Active shard namespace; falls back to `region` for non-sharded paths. */
    public static function shardKey(): string
    {
        if (app()->bound('shard_key')) {
            return strtolower((string) app('shard_key'));
        }

        return app()->bound('region') ? strtolower((string) app('region')) : '';
    }

    public static function resolveFromRequest(Request $request): ?InfrastructureNode
    {
        try {
            $iso2 = $request->header('X-Country');

            if ($iso2) {
                $node = self::byCountryCode($iso2);

                if ($node) {
                    return $node;
                }
            }

            if (session()->has('active_shard_id')) {
                $node = InfrastructureNode::query()
                    ->where('id', session('active_shard_id'))
                    ->where('is_active', true)
                    ->first();

                if ($node) {
                    return $node;
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function byCountryCode(string $iso2): ?InfrastructureNode
    {
        return InfrastructureNode::query()
            ->where('type', 'country')
            ->where('is_active', true)
            ->whereRaw('LOWER(country_code) = ?', [strtolower($iso2)])
            ->first();
    }

    public static function defaultCountry(): ?InfrastructureNode
    {
        return InfrastructureNode::query()
            ->where('type', 'country')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    public static function current(): ?InfrastructureNode
    {
        return ShardContext::current();
    }

    public static function currency(): string
    {
        if (app()->bound('shard_currency')) {
            return (string) app('shard_currency');
        }

        $node = self::current();

        return $node ? self::nodeCurrency($node) : self::DEFAULT_CURRENCY;
    }

    public static function connection()
    {
        $node = self::current();

        if ($node) {
            self::activate($node);
        }

        return DB::connection(TenantConnection::NAME);
    }

    private static function nodeCurrency(InfrastructureNode $node): string
    {
        $code = $node->getAttribute('currency_code');

        return $code ? strtoupper((string) $code) : self::DEFAULT_CURRENCY;
    }
}
