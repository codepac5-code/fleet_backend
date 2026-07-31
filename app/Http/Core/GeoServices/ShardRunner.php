<?php

namespace App\Http\Core\GeoServices;

use App\Models\InfrastructureNode;
use Illuminate\Support\Facades\Log;

class ShardRunner
{
    public static function eachCountry(callable $callback): void
    {
        // Only provisioned shards have a usable database. Skipping the rest keeps a
        // just-created (or mis-credentialed) country from being touched at all.
        $nodes = InfrastructureNode::query()
            ->where('type', 'country')
            ->where('is_active', true)
            ->whereNotNull('provisioned_at')
            ->get();

        foreach ($nodes as $node) {
            try {
                self::configure($node);
                $callback($node);
            } catch (\Throwable $e) {
                // A single unreachable / bad-credentials shard must NEVER kill a
                // long-running per-shard daemon (the SLA sweep died on `fleet_tr`
                // with "Access denied", taking every other daemon down with it).
                // Log and move on.
                Log::warning('ShardRunner: skipped shard ' . ($node->country_code ?? $node->id) . ' — ' . $e->getMessage());
            }
        }
    }

    public static function configure(InfrastructureNode $node): void
    {
        ShardManager::activate($node);
    }
}
