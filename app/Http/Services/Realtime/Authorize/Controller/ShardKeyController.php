<?php

namespace App\Http\Services\Realtime\Authorize\Controller;

use App\Http\Core\GeoServices\ShardManager;
use Illuminate\Http\JsonResponse;

/**
 * `GET /realtime/shard` — the namespace this caller's rooms live under.
 *
 * Both apps used to derive it themselves by lowercasing the country they sent,
 * which agreed with the server only by coincidence: a device sending `US` built
 * `us.booking.40` while the server published `sa.booking.40`, so the rider
 * simply never heard a thing. And because the namespace was the COUNTRY while
 * ids are minted per DATABASE, SA and QA — which share `fleet` — could not see
 * each other's drivers at all.
 *
 * The shard is the server's to decide, so the server states it. Clients ask
 * once at boot and use the answer verbatim.
 */
class ShardKeyController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['shard' => ShardManager::shardKey()]);
    }
}
