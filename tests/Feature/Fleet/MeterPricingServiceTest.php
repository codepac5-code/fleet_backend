<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Pricing\MeterPricingService;
use App\Models\OfficeSubServicePrice;
use App\Models\SubService;

/**
 * Meter pricing (the direct-to-driver style): open + per-km + per-minute, from
 * the sub-service's rates or an office override. Distance/duration are given;
 * the coordinate path is [RouteEstimator]'s job and covered there.
 */
class MeterPricingServiceTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2024_10_29_211028_create_offices_table.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
    ];

    private MeterPricingService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        app()->instance('shard_currency', 'SYP');
        $this->svc = new MeterPricingService();
        \App\Models\Service::query()->create(['id' => 1, 'image' => 'x', 'title' => 'Ride', 'title_en' => 'Ride', 'status' => 1]);
    }

    private function subService(int $id, float $open, float $km, float $minute): void
    {
        SubService::query()->create([
            'id' => $id, 'name' => "S$id", 'name_en' => "S$id", 'serviceId' => 1,
            'openPrice' => $open, 'kmPrice' => $km, 'minutePrice' => $minute, 'is_travel' => false, 'status' => 1,
        ]);
    }

    public function test_prices_from_the_sub_service_base_rates(): void
    {
        // open 5.00, 1.50/km, 0.25/min.
        $this->subService(1, 5, 1.5, 0.25);

        // 10 km (10,000 m), 20 min (1200 s) → 5 + 15 + 5 = 25.00 → 2500 minor.
        $q = $this->svc->quote(0, 1, 10000, 1200);

        $this->assertSame(2500, $q['fare_minor']);
        $this->assertSame('meter', $q['pricing_style']);
        $this->assertSame('SYP', $q['currency_code']);
        $this->assertSame(500, $q['breakdown']['open']);
        $this->assertSame(1500, $q['breakdown']['distance']);
        $this->assertSame(500, $q['breakdown']['time']);
    }

    public function test_office_override_beats_the_base_rate(): void
    {
        $this->subService(1, 5, 1.5, 0.25);
        $office = \App\Models\Office::query()->create([
            'officeName' => 'Cheap Co', 'email' => 'c@x.sy', 'password' => 'x',
            'contactNumber' => '1', 'address' => 'a', 'country' => 'SY', 'city' => 'Damascus', 'region' => 'r', 'status' => 1,
        ]);
        // This office undercuts: open 3.00, 1.00/km, 0.00/min.
        OfficeSubServicePrice::query()->create([
            'office_id' => $office->id, 'sub_service_id' => 1, 'openPrice' => 3, 'kmPrice' => 1, 'minutePrice' => 0,
        ]);

        // With override: 3 + 10×1 + 0 = 13.00 → 1300. Base would be 2500.
        $this->assertSame(1300, $this->svc->quote((int) $office->id, 1, 10000, 1200)['fare_minor']);
        // An office with no override still pays the base rate.
        $this->assertSame(2500, $this->svc->quote((int) $office->id + 1, 1, 10000, 1200)['fare_minor']);
    }

    public function test_zero_distance_and_time_is_just_the_open_price(): void
    {
        $this->subService(1, 5, 1.5, 0.25);
        $this->assertSame(500, $this->svc->quote(0, 1, 0, 0)['fare_minor']);
    }

    public function test_unknown_sub_service_is_rejected(): void
    {
        $this->expectExceptionMessage('sub service not found');
        $this->svc->quote(0, 999, 1000, 60);
    }
}
