<?php

namespace App\Http\Middleware;

use App\Http\Core\GeoServices\ShardManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pin every request to a country shard.
 *
 * This used to leave the shard unresolved whenever `X-Country` named a country
 * with no `infrastructure_nodes` row — the request then ran on the default DB
 * connection with `app('region')` NEVER BOUND. That is not harmless: the region
 * is what namespaces realtime channels, so those requests emitted `booking.40` /
 * `user.91` while every properly-resolved request emitted `qa.booking.41`. Two
 * channel namespaces for the same system means a client listening on one never
 * hears events published on the other, and the whole failure was silent.
 *
 * Now an unresolved country falls back to the DEFAULT node explicitly, so the
 * region is always bound and channel names are always consistent; and an
 * unrecognised `X-Country` is logged instead of swallowed.
 */
class ResolveTenantShard
{
    public function handle(Request $request, Closure $next): Response
    {
        $node = ShardManager::resolveFromRequest($request);

        // Only rescue the case that actually caused the split namespace: a
        // client DID name a country and it matched no node. A request with no
        // `X-Country` at all is left exactly as before — activating a shard for
        // it would repoint the tenant connection under every test, which run
        // headerless against their own sqlite connection.
        if ($node === null && (string) $request->header('X-Country', '') !== '') {
            Log::warning(
                'Unknown X-Country "' . $request->header('X-Country') . '" — falling back to the default shard.'
            );

            $node = ShardManager::defaultCountry();
        }

        if ($node !== null) {
            ShardManager::activate($node);
        }

        return $next($request);
    }
}
