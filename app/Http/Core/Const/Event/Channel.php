<?php

namespace App\Http\Core\Const\Event;

use App\Http\Core\GeoServices\ShardManager;

/**
 * Realtime channel (room) names. Every channel is namespaced by the active
 * region (the driver's country shard, e.g. `sy.driver.33`) so entities sharing
 * a numeric id across countries never collide on one room. The region segment
 * is derived from the active shard (`app('region')`, set by
 * ShardManager::activate) at the moment the event is created — always inside a
 * resolved shard context. A dot separator keeps the gateway frame
 * `"{room}:{event}"` parseable (the room itself carries no colon).
 */
class Channel
{
    public static function user(int $id): string
    {
        return self::prefix() . 'user.' . $id;
    }

    public static function driver(int $id): string
    {
        return self::prefix() . 'driver.' . $id;
    }

    public static function office(int $id): string
    {
        return self::prefix() . 'office.' . $id;
    }

    public static function booking(int $id): string
    {
        return self::prefix() . 'booking.' . $id;
    }

    /**
     * Fleet-wide operations room for the active shard (e.g. `sy.admin`). Unlike
     * the id-scoped rooms this carries no id — every admin watching this shard
     * joins the same room. Used to surface office/fleet-facing events (new
     * orders, SOS, escalations) live on the panel.
     */
    public static function admin(): string
    {
        return self::prefix() . 'admin';
    }

    /** Active region + separator (e.g. `sy.`), or empty when no shard is active. */
    private static function prefix(): string
    {
        // The SHARD, not the country: SA and QA share database `fleet`, so
        // keying rooms by country split one id space in two and events never
        // reached the other half. See ShardManager::shardKeyFor().
        $key = ShardManager::shardKey();

        return $key !== '' ? $key . '.' : '';
    }
}
