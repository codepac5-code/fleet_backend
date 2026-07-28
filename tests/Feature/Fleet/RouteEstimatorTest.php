<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Dispatch\Geo;
use App\Http\Core\GeoServices\RouteEstimator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Real road distance for pricing, with a haversine fallback that guarantees a
 * quote even when Google is unreachable or unconfigured.
 */
class RouteEstimatorTest extends FleetTestCase
{
    private RouteEstimator $est;

    protected function setUp(): void
    {
        parent::setUp();
        $this->est = new RouteEstimator();
        Cache::flush();
    }

    // Damascus: Umayyad Sq → Airport-ish.
    private const P = [33.5138, 36.2765];
    private const D = [33.4900, 36.3300];

    public function test_falls_back_to_haversine_with_no_api_key(): void
    {
        config(['services.google_maps.key' => '']);
        Http::fake(); // any call would fail the test — none should be made.

        [$dist, $dur] = $this->est->estimate(self::P[0], self::P[1], self::D[0], self::D[1]);

        $expected = Geo::haversineMeters(self::P[0], self::P[1], self::D[0], self::D[1]);
        $this->assertSame($expected, $dist);
        $this->assertSame((int) round($expected / 8), $dur);
        Http::assertNothingSent();
    }

    public function test_uses_the_real_driving_distance_when_directions_answers(): void
    {
        config(['services.google_maps.key' => 'test-key']);
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'routes' => [['legs' => [['distance' => ['value' => 7400], 'duration' => ['value' => 960]]]]],
            ]),
        ]);

        [$dist, $dur] = $this->est->estimate(self::P[0], self::P[1], self::D[0], self::D[1]);

        // The real road route is longer than the straight line — the whole point.
        $this->assertSame(7400, $dist);
        $this->assertSame(960, $dur);
        $this->assertGreaterThan(Geo::haversineMeters(self::P[0], self::P[1], self::D[0], self::D[1]), $dist);
    }

    public function test_sums_every_leg_across_waypoints(): void
    {
        config(['services.google_maps.key' => 'test-key']);
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'routes' => [['legs' => [
                    ['distance' => ['value' => 3000], 'duration' => ['value' => 400]],
                    ['distance' => ['value' => 4500], 'duration' => ['value' => 600]],
                ]]],
            ]),
        ]);

        [$dist, $dur] = $this->est->estimate(self::P[0], self::P[1], self::D[0], self::D[1], [[33.50, 36.30]]);

        $this->assertSame(7500, $dist);
        $this->assertSame(1000, $dur);
    }

    public function test_falls_back_when_directions_has_no_route(): void
    {
        config(['services.google_maps.key' => 'test-key']);
        Http::fake([
            'maps.googleapis.com/*' => Http::response(['status' => 'ZERO_RESULTS', 'routes' => []]),
        ]);

        [$dist] = $this->est->estimate(self::P[0], self::P[1], self::D[0], self::D[1]);

        $this->assertSame(Geo::haversineMeters(self::P[0], self::P[1], self::D[0], self::D[1]), $dist);
    }

    public function test_falls_back_when_the_api_errors(): void
    {
        config(['services.google_maps.key' => 'test-key']);
        Http::fake(fn () => throw new \RuntimeException('network down'));

        [$dist] = $this->est->estimate(self::P[0], self::P[1], self::D[0], self::D[1]);

        $this->assertSame(Geo::haversineMeters(self::P[0], self::P[1], self::D[0], self::D[1]), $dist);
    }
}
