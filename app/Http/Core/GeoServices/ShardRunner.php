<?php

namespace App\Http\Core\GeoServices;

use App\Models\InfrastructureNode;

class ShardRunner
{
    public static function eachCountry(callable $callback): void
    {
        $nodes = InfrastructureNode::query()
            ->where('type', 'country')
            ->where('is_active', true)
            ->get();

        foreach ($nodes as $node) {
            self::configure($node);
            $callback($node);
        }
    }

    public static function configure(InfrastructureNode $node): void
    {
        ShardManager::activate($node);
    }
}
