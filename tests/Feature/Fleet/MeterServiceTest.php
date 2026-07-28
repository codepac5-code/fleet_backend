<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Classes\Ride\MeterService;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Models\RideBooking;
use App\Models\ServiceTariff;

/**
 * The server-side live meter: GPS pings fold into a running distance + fare that
 * both apps watch tick. Distance is accumulated from ping-to-ping deltas so a
 * client can't report an inflated total.
 */
class MeterServiceTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_17_000003_add_arrived_at_to_ride_bookings.php',
        '2026_07_23_000002_add_live_meter_to_ride_bookings.php',
    ];

    private MeterService $meter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->meter = new MeterService(new TariffResolver(), new PricingService());

        ServiceTariff::query()->create([
            'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard', 'currency_code' => 'USD',
            'pricing_style' => 'meter', 'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30, 'minimum_minor' => 0,
        ]);
    }

    private function onTripBooking(): RideBooking
    {
        $b = new RideBooking();
        $b->forceFill([
            'id' => 700, 'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => BookingStatus::ON_TRIP, 'currency_code' => 'USD',
            'trip_started_at' => now()->subMinutes(2), 'meter_distance_m' => 0,
        ]);

        return $b;
    }

    public function test_first_ping_anchors_without_distance(): void
    {
        $b = $this->onTripBooking();
        $snap = $this->meter->tick($b, 25.2854, 51.5310);

        $this->assertNotNull($snap);
        $this->assertSame(0, $snap['distance_m']);
        $this->assertSame(25.2854, (float) $b->meter_last_lat);
    }

    public function test_second_ping_accumulates_distance_and_prices_it(): void
    {
        $b = $this->onTripBooking();
        $this->meter->tick($b, 25.2854, 51.5310);            // anchor
        $snap = $this->meter->tick($b, 25.3854, 51.5310);    // ~11 km north

        $this->assertGreaterThan(9000, $snap['distance_m']);  // ~1 deg lat ≈ 111 km → 0.1 deg ≈ 11 km
        $this->assertGreaterThanOrEqual(120, $snap['elapsed_s']); // started 2 min ago
        // fare = base 500 + 200/km × ~11km + 30/min × ~2min > base.
        $this->assertGreaterThan(500, $snap['fare_minor']);
    }

    public function test_returns_null_when_not_a_running_metered_trip(): void
    {
        $b = $this->onTripBooking();
        $b->status = BookingStatus::ARRIVED; // not ON_TRIP
        $this->assertNull($this->meter->tick($b, 25.2854, 51.5310));

        $b2 = $this->onTripBooking();
        $b2->pricing_style = 'fixed'; // corridor, no meter
        $this->assertNull($this->meter->tick($b2, 25.2854, 51.5310));
    }
}
