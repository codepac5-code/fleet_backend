<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Pricing\PricingService;
use Tests\TestCase;

class PricingTest extends TestCase
{
    private PricingService $pricing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricing = new PricingService();
    }

    private function meterTariff(): array
    {
        return [
            'pricing_style' => 'meter',
            'base_minor' => 500,
            'per_km_minor' => 200,
            'per_minute_minor' => 30,
            'minimum_minor' => 1000,
        ];
    }

    public function test_meter_fare_sums_base_distance_and_time(): void
    {
        $q = $this->pricing->quote($this->meterTariff(), 3000, 600);

        $this->assertSame(1400, $q['fare_minor']);
        $this->assertSame(600, $q['breakdown']['distance']);
        $this->assertSame(300, $q['breakdown']['time']);
        $this->assertFalse($q['breakdown']['minimum_applied']);
    }

    public function test_minimum_fare_is_enforced(): void
    {
        $q = $this->pricing->quote($this->meterTariff(), 500, 120);

        $this->assertSame(1000, $q['fare_minor']);
        $this->assertTrue($q['breakdown']['minimum_applied']);
    }

    public function test_fixed_style_returns_flat_price(): void
    {
        $q = $this->pricing->quote(['pricing_style' => 'fixed', 'fixed_minor' => 5000], 99999, 99999);

        $this->assertSame('fixed', $q['pricing_style']);
        $this->assertSame(5000, $q['fare_minor']);
    }

    public function test_negative_inputs_are_clamped(): void
    {
        $q = $this->pricing->quote($this->meterTariff(), -100, -100);

        $this->assertSame(1000, $q['fare_minor']);
    }
}
