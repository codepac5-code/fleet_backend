<?php

namespace App\Http\Core\GeoServices;

use App\Http\Core\Classes\Dispatch\Geo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Real road distance + duration for pricing, from the Google Directions API.
 *
 * Pricing used a straight-line haversine distance and a flat 8 m/s assumption,
 * which under-quotes any trip that can't fly over rivers and one-way streets.
 * This asks Google for the actual driving route instead.
 *
 * It NEVER lets pricing fail: with no API key, a network error, a non-OK
 * response or an empty result, it falls back to the same haversine estimate the
 * pipeline used before — so a missing key degrades quality, never availability.
 * Results are cached by rounded coordinates so repeated quotes for the same
 * route (offers list, re-price on decline) don't re-hit the API.
 *
 * Returns `[distanceMeters, durationSeconds]` — the same tuple the services'
 * old private `route()` produced, so it drops in without touching callers.
 */
class RouteEstimator
{
    /** ~8 m/s (29 km/h) city pace — the fallback duration when only distance is known. */
    private const FALLBACK_SPEED_MPS = 8;

    private const CACHE_TTL_SECONDS = 21600; // 6h — a road route is stable for the day.

    private const HTTP_TIMEOUT_SECONDS = 4;

    /**
     * Driving distance (m) + duration (s) from pickup → (waypoints) → dropoff.
     *
     * @param array<int, array{0: float, 1: float}> $waypoints [lat, lng] pairs in order
     * @return array{0: int, 1: int}
     */
    public function estimate(float $pLat, float $pLng, float $dLat, float $dLng, array $waypoints = []): array
    {
        $key = (string) config('services.google_maps.key');
        if ($key === '') {
            return $this->haversine($pLat, $pLng, $dLat, $dLng, $waypoints);
        }

        $cacheKey = $this->cacheKey($pLat, $pLng, $dLat, $dLng, $waypoints);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($pLat, $pLng, $dLat, $dLng, $waypoints, $key) {
            try {
                $result = $this->fetchDirections($pLat, $pLng, $dLat, $dLng, $waypoints, $key);
                if ($result !== null) {
                    return $result;
                }
            } catch (Throwable $e) {
                Log::warning('RouteEstimator: directions failed, using haversine — ' . $e->getMessage());
            }

            return $this->haversine($pLat, $pLng, $dLat, $dLng, $waypoints);
        });
    }

    /**
     * @param array<int, array{0: float, 1: float}> $waypoints
     * @return array{0: int, 1: int}|null null when the API answered but with no usable route.
     */
    private function fetchDirections(float $pLat, float $pLng, float $dLat, float $dLng, array $waypoints, string $key): ?array
    {
        $params = [
            'origin' => "$pLat,$pLng",
            'destination' => "$dLat,$dLng",
            'mode' => 'driving',
            'key' => $key,
        ];
        if ($waypoints !== []) {
            $params['waypoints'] = implode('|', array_map(fn ($w) => $w[0] . ',' . $w[1], $waypoints));
        }

        $response = Http::timeout(self::HTTP_TIMEOUT_SECONDS)
            ->get('https://maps.googleapis.com/maps/api/directions/json', $params)
            ->json();

        if (($response['status'] ?? null) !== 'OK' || empty($response['routes'])) {
            return null;
        }

        // Sum every leg (origin → each waypoint → destination).
        $distance = 0;
        $duration = 0;
        foreach ($response['routes'][0]['legs'] ?? [] as $leg) {
            $distance += (int) ($leg['distance']['value'] ?? 0);
            $duration += (int) ($leg['duration']['value'] ?? 0);
        }

        if ($distance <= 0) {
            return null;
        }

        return [$distance, $duration > 0 ? $duration : (int) round($distance / self::FALLBACK_SPEED_MPS)];
    }

    /**
     * Straight-line distance through the ordered points, at the flat city pace —
     * the exact behaviour the services had before, preserved as the fallback.
     *
     * @param array<int, array{0: float, 1: float}> $waypoints
     * @return array{0: int, 1: int}
     */
    private function haversine(float $pLat, float $pLng, float $dLat, float $dLng, array $waypoints): array
    {
        $points = [[$pLat, $pLng], ...$waypoints, [$dLat, $dLng]];
        $distance = 0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $distance += Geo::haversineMeters($points[$i][0], $points[$i][1], $points[$i + 1][0], $points[$i + 1][1]);
        }

        return [$distance, (int) round($distance / self::FALLBACK_SPEED_MPS)];
    }

    /**
     * @param array<int, array{0: float, 1: float}> $waypoints
     */
    private function cacheKey(float $pLat, float $pLng, float $dLat, float $dLng, array $waypoints): string
    {
        // ~5 decimals ≈ 1 m — enough to dedupe identical quotes without over-caching.
        $r = fn (float $v) => number_format($v, 5, '.', '');
        $wp = implode(';', array_map(fn ($w) => $r($w[0]) . ',' . $r($w[1]), $waypoints));

        return "route:{$r($pLat)},{$r($pLng)}:{$r($dLat)},{$r($dLng)}:{$wp}";
    }
}
