<?php
namespace App\Http\Core\GeoServices;

use App\Models\InfrastructureNode;


class ShardContext
{
    protected static ?InfrastructureNode $node = null;

    public static function set($node)
    {
        self::$node = $node;

        app()->instance('active_shard', $node);
    }

    public static function current()
    {
        if (self::$node !== null) {
            return self::$node;
        }

        return app()->bound('active_shard') ? app('active_shard') : null;
    }

    public static function clear(): void
    {
        self::$node = null;

        if (app()->bound('active_shard')) {
            app()->forgetInstance('active_shard');
        }
    }
}
