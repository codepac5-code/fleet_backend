<?php

namespace App\Http\Core\Classes\Dispatch;

use App\Http\Core\Classes\Settings\AppSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

/**
 * Live driver positions in Redis — the source of truth for proximity dispatch.
 *
 * Two keys per region (the driver's country shard), written by the realtime
 * gateway the moment a `driver.location` arrives (so its ACK means "stored"):
 *
 *   fleet:geo:{region}            GEO set, member = driverId  → radius search
 *   fleet:loc:{region}:{driverId} JSON {lat,lng,ts}, EXPIRE   → per-driver TTL
 *
 * Redis GEO members cannot carry their own TTL, so the per-driver key is the
 * liveness marker: when it expires (or the driver goes offline and we delete
 * it) the geo member is pruned lazily on the next search. Going offline removes
 * both immediately, so an unavailable driver can never be matched.
 */
class DriverLocationStore
{
    public const CONNECTION = 'default';

    /** Default retention for a position (seconds) — refreshed on every update. */
    public const DEFAULT_TTL = 3600; // 1 hour

    public static function geoKey(string $region): string
    {
        return 'fleet:geo:' . strtolower($region);
    }

    public static function locKey(string $region, int $driverId): string
    {
        return 'fleet:loc:' . strtolower($region) . ':' . $driverId;
    }

    public static function ttlSeconds(): int
    {
        return AppSettings::int('driver_location_ttl_s', self::DEFAULT_TTL);
    }

    private static function conn()
    {
        return Redis::connection(self::CONNECTION);
    }

    /**
     * Store/refresh a driver's position (also refreshes its TTL).
     * Fail-soft: a Redis hiccup must not take down a presence update — the
     * driver simply keeps their previous position until the next fix lands.
     */
    public static function put(string $region, int $driverId, float $lat, float $lng, ?int $ttl = null): void
    {
        $ttl = $ttl ?? self::ttlSeconds();

        try {
            $conn = self::conn();
            $conn->executeRaw(['GEOADD', self::geoKey($region), (string) $lng, (string) $lat, (string) $driverId]);
            $conn->executeRaw([
                'SET', self::locKey($region, $driverId),
                json_encode(['lat' => $lat, 'lng' => $lng, 'ts' => time()]),
                'EX', (string) $ttl,
            ]);
        } catch (Throwable $e) {
            Log::warning('DriverLocationStore::put failed — ' . $e->getMessage());
        }
    }

    /** Drop a driver's position entirely (driver went offline). */
    public static function forget(string $region, int $driverId): void
    {
        try {
            $conn = self::conn();
            $conn->executeRaw(['ZREM', self::geoKey($region), (string) $driverId]);
            $conn->executeRaw(['DEL', self::locKey($region, $driverId)]);
        } catch (Throwable $e) {
            // never block a presence change on a cache failure
        }
    }

    /** Current position, or null when absent/expired. */
    public static function get(string $region, int $driverId): ?array
    {
        $raw = self::conn()->executeRaw(['GET', self::locKey($region, $driverId)]);

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) && isset($data['lat'], $data['lng']) ? $data : null;
    }

    /**
     * Drivers within `$radiusMeters` of a point, nearest first.
     * Expired members (their TTL key is gone) are pruned as we go.
     *
     * @return array<int, array{driver_id:int, distance_m:float}>
     */
    public static function search(string $region, float $lat, float $lng, float $radiusMeters, int $limit = 50): array
    {
        try {
            $conn = self::conn();
            $geoKey = self::geoKey($region);

            $rows = $conn->executeRaw([
                'GEOSEARCH', $geoKey,
                'FROMLONLAT', (string) $lng, (string) $lat,
                'BYRADIUS', (string) $radiusMeters, 'm',
                'ASC', 'COUNT', (string) max($limit * 3, $limit),
                'WITHDIST',
            ]);
        } catch (Throwable $e) {
            // Degrade to "nobody nearby" rather than failing the dispatch tick.
            Log::warning('DriverLocationStore::search failed — ' . $e->getMessage());

            return [];
        }

        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        $stale = [];

        foreach ($rows as $row) {
            // Each row is [member, distance] (WITHDIST).
            $driverId = (int) (is_array($row) ? ($row[0] ?? 0) : $row);
            $distance = (float) (is_array($row) ? ($row[1] ?? 0) : 0);

            if ($driverId <= 0) {
                continue;
            }

            // Liveness: the TTL key must still exist.
            if (! $conn->executeRaw(['EXISTS', self::locKey($region, $driverId)])) {
                $stale[] = (string) $driverId;
                continue;
            }

            $out[] = ['driver_id' => $driverId, 'distance_m' => $distance];

            if (count($out) >= $limit) {
                break;
            }
        }

        if ($stale !== []) {
            $conn->executeRaw(array_merge(['ZREM', $geoKey], $stale));
        }

        return $out;
    }
}
