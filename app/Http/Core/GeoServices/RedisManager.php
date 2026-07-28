<?php

namespace App\Http\Core\GeoServices;

use Illuminate\Support\Facades\Redis;

class RedisManager
{
    public static function connection()
    {
        $node = ShardContext::current();

        config([
            'database.redis.default.host' => $node->redis_host,
            'database.redis.default.database' => $node->redis_db,
        ]);

        return Redis::connection();
    }
}
