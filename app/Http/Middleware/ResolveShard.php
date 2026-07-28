<?php
namespace App\Http\Middleware;

use App\Http\Core\GeoServices\ShardContext;
use App\Models\InfrastructureNode;
use Closure;

class ResolveShard
{
    public function handle($request, Closure $next)
    {
        $node = null;

        if (session()->has('active_shard_id')) {

            $node = InfrastructureNode::query()
                ->where('id', session('active_shard_id'))
                ->where('is_active', true)
                ->first();
        }

        if (!$node) {

            $node = InfrastructureNode::query()
                ->where('type', 'country')
                ->where('is_active', true)
                ->first();
        }

        ShardContext::set($node);

        return $next($request);
    }
}
