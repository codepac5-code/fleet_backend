<?php

namespace App\Http\Middleware\Panel;

use App\Http\Core\GeoServices\ShardAggregator;
use App\Http\Core\GeoServices\ShardContext;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\InfrastructureNode;
use Closure;
use Illuminate\Support\Facades\DB;

class ConfigureCountryDatabase
{
    public function handle($request, Closure $next)
    {
        // A per-request `country` (InfrastructureNode id) overrides the session
        // scope — used by row actions (view/edit/delete/create) opened from the
        // aggregate "All countries" list so they target the correct shard.
        $override = $request->input('country');

        if ($override !== null && $override !== '' && $override !== ShardAggregator::SCOPE) {
            $node = $this->nodeById((int) $override);

            if ($node) {
                $this->activateSingle($node);

                return $next($request);
            }
        }

        if (session('active_shard_id') === ShardAggregator::SCOPE) {
            ShardAggregator::activate();

            return $next($request);
        }

        $node = $this->resolveNode();

        if ($node) {
            $this->activateSingle($node);
        }

        return $next($request);
    }

    private function activateSingle(InfrastructureNode $node): void
    {
        app()->instance('shard_all', false);

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

        // Drop any guard resolved on the PREVIOUS connection. Laravel's auth
        // middleware can run before this one (its Authenticate isn't in the
        // middleware-priority list, so route-group order isn't guaranteed), which
        // would resolve the office/employee from `dynamic` while it still pointed
        // at the default shard — and since every shard has an office id=1, cache
        // the WRONG office. Forgetting the guards forces a fresh resolve on the
        // shard we just activated.
        \Illuminate\Support\Facades\Auth::forgetGuards();

        // Bind the same shard namespace the APP sets on ShardManager::activate, so
        // the panel reads Redis (order board) under the identical prefix the app
        // wrote it with. Without this the panel's shardKey() is empty while the
        // app's is the db_name → the board would read a different key and show
        // nothing (and cross-country isolation could not be keyed consistently).
        app()->instance('region', strtolower((string) ($node->country_code ?? '')));
        app()->instance('shard_key', \App\Http\Core\GeoServices\ShardManager::shardKeyFor($node));
    }

    private function nodeById(int $id): ?InfrastructureNode
    {
        try {
            return InfrastructureNode::query()
                ->where('id', $id)
                ->where('type', 'country')
                ->where('is_active', true)
                ->whereNotNull('provisioned_at')
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveNode(): ?InfrastructureNode
    {
        try {
            // Only a PROVISIONED shard may be activated. A country created but not
            // yet provisioned has no database — activating it would crash every
            // tenant query with "Unknown database". So an unprovisioned session
            // shard is ignored and we fall back to the first provisioned country.
            if (session()->has('active_shard_id')) {
                $node = InfrastructureNode::query()
                    ->where('id', session('active_shard_id'))
                    ->where('is_active', true)
                    ->whereNotNull('provisioned_at')
                    ->first();

                if ($node) {
                    return $node;
                }
            }

            return InfrastructureNode::query()
                ->where('type', 'country')
                ->where('is_active', true)
                ->whereNotNull('provisioned_at')
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
