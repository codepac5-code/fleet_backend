<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use App\Http\Core\GeoServices\ShardContext;

class ConfigureDatabase
{
    public function handle($request, Closure $next)
    {
        $node = ShardContext::current();

        if (!$node) {
            return $next($request);
        }

        config([
            'database.connections.dynamic.host'     => $node->db_host,
            'database.connections.dynamic.port'     => $node->db_port,
            'database.connections.dynamic.database' => $node->db_name,
            'database.connections.dynamic.username' => $node->db_user,
            'database.connections.dynamic.password' => $node->db_pass,
        ]);

        DB::purge('dynamic');
        DB::reconnect('dynamic');

        return $next($request);
    }
}
