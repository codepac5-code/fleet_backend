<?php
namespace App\Http\Middleware;
use App\Http\Core\GeoServices\ShardContext;
use App\Models\InfrastructureNode;
use GeoResolver;
use Closure;


class ResolveShard
{
    public function handle($request, Closure $next)
    {
        $node = null;

        if (
            auth('admin')->check() &&
            session()->has('active_shard_id')
        ) {

            $node = InfrastructureNode::find(
                session('active_shard_id')
            );
        }

        if (
            !$node &&
            $request->has('lat') &&
            $request->has('lng')
        ) {

            $node = app(GeoResolver::class)
                ->resolve(
                    $request->lat,
                    $request->lng
                );
        }

        if (!$node) {

            $node = InfrastructureNode::query()
                ->where('type', 'country')
                ->first();
        }

        ShardContext::set($node);


        return $next($request);
    }
}



        // $userShard = Cache::get("user:{$userId}:shard");

        // if ($userShard) {
        //     $node = InfrastructureNode::find($userShard);
        // } else {
        //     $node = app(GeoResolver::class)->resolve($lat, $lng);

        //     Cache::put("user:{$userId}:shard", $node->id);
        // }
