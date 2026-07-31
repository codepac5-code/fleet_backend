<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\Service;
use App\Models\ServiceTariff;
use App\Models\SubService;

/**
 * The bridge that unifies meter pricing onto the sub-service catalog: a booking
 * tied to a sub-service prices from `office_sub_service_prices` → `sub_services`,
 * shaped like a ServiceTariff so PricingService prices it identically.
 */
class TariffBridgeTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2024_10_29_211028_create_offices_table.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
    ];

    private TariffResolver $resolver;
    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        app()->instance('shard_currency', 'SYP');
        $this->resolver = new TariffResolver();
        $this->pricing = new PricingService();
        Service::query()->create(['id' => 1, 'image' => 'x', 'title' => 'Ride', 'title_en' => 'Ride', 'status' => 1]);
        SubService::query()->create([
            'id' => 1, 'name' => 'S1', 'name_en' => 'S1', 'serviceId' => 1,
            'openPrice' => 5, 'kmPrice' => 1.5, 'minutePrice' => 0.25, 'is_travel' => false, 'status' => 1,
        ]);
    }

    public function test_prices_a_sub_service_from_catalog_base_rates(): void
    {
        $tariff = $this->resolver->forOfficeSubService(0, 1);

        $this->assertSame('meter', $tariff['pricing_style']);
        $this->assertSame('SYP', $tariff['currency_code']);
        $this->assertSame(500, $tariff['base_minor']);       // 5.00
        $this->assertSame(150, $tariff['per_km_minor']);     // 1.50
        $this->assertSame(25, $tariff['per_minute_minor']);  // 0.25

        // 10 km, 20 min → 500 + 150×10 + 25×20 = 2500.
        $this->assertSame(2500, $this->pricing->quote($tariff, 10000, 1200)['fare_minor']);
    }

    public function test_office_override_beats_the_catalog_base(): void
    {
        $office = Office::query()->create([
            'officeName' => 'Cheap Co', 'email' => 'c@x.sy', 'password' => 'x',
            'contactNumber' => '1', 'address' => 'a', 'country' => 'SY', 'city' => 'Damascus', 'region' => 'r', 'status' => 1,
        ]);
        OfficeSubServicePrice::query()->create([
            'office_id' => $office->id, 'sub_service_id' => 1,
            'openPrice' => 3, 'kmPrice' => 1, 'minutePrice' => 0,
        ]);

        $tariff = $this->resolver->forOfficeSubService((int) $office->id, 1);

        $this->assertSame(300, $tariff['base_minor']);
        $this->assertSame(100, $tariff['per_km_minor']);
        $this->assertSame(0, $tariff['per_minute_minor']);
    }

    public function test_falls_back_to_service_tariff_without_a_sub_service(): void
    {
        ServiceTariff::query()->create([
            'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard', 'currency_code' => 'SYP',
            'pricing_style' => 'meter', 'base_minor' => 700, 'per_km_minor' => 200, 'per_minute_minor' => 30, 'minimum_minor' => 0,
        ]);

        // No sub-service → the per-office ServiceTariff.
        $viaTariff = $this->resolver->forOfficeServiceOrSub(3, null, 'ride', 'standard');
        $this->assertSame(700, $viaTariff['base_minor']);

        // With a sub-service → the catalog wins.
        $viaSub = $this->resolver->forOfficeServiceOrSub(3, 1, 'ride', 'standard');
        $this->assertSame(500, $viaSub['base_minor']);
    }

    public function test_office_published_price_wins_from_a_class_string_alone(): void
    {
        // An office publishes a price for the sub-service on its "my services"
        // screen; a ServiceTariff also exists for the same class.
        $office = Office::query()->create([
            'officeName' => 'Souq Rides', 'email' => 's@x.sy', 'password' => 'x',
            'contactNumber' => '1', 'address' => 'a', 'country' => 'SY', 'city' => 'Damascus', 'region' => 'r', 'status' => 1,
        ]);
        OfficeSubServicePrice::query()->create([
            'office_id' => $office->id, 'sub_service_id' => 1,
            'openPrice' => 4, 'kmPrice' => 1, 'minutePrice' => 0,
        ]);
        ServiceTariff::query()->create([
            'office_id' => $office->id, 'service' => 'ride', 'service_class' => 'S1', 'currency_code' => 'SYP',
            'pricing_style' => 'meter', 'base_minor' => 900, 'per_km_minor' => 400, 'per_minute_minor' => 90, 'minimum_minor' => 0,
        ]);

        // The caller passes only the class STRING (the sub-service name), no id —
        // the office's published price must still win over the ServiceTariff.
        $tariff = $this->resolver->forOfficeServiceOrSub((int) $office->id, null, 'ride', 'S1');
        $this->assertSame(400, $tariff['base_minor']);   // office price, not the 900 tariff
        $this->assertSame(100, $tariff['per_km_minor']);

        // An office that never published a price still falls back to ServiceTariff.
        ServiceTariff::query()->create([
            'office_id' => 4, 'service' => 'ride', 'service_class' => 'S1', 'currency_code' => 'SYP',
            'pricing_style' => 'meter', 'base_minor' => 900, 'per_km_minor' => 400, 'per_minute_minor' => 90, 'minimum_minor' => 0,
        ]);
        $fallback = $this->resolver->forOfficeServiceOrSub(4, null, 'ride', 'S1');
        $this->assertSame(900, $fallback['base_minor']);
    }
}
