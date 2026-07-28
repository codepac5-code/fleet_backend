<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\ChannelAuthorizer;
use App\Models\RideBooking;

class ChannelAuthorizerBookingTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private ChannelAuthorizer $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new ChannelAuthorizer();

        $booking = RideBooking::query()->create([
            'user_id' => 5, 'office_id' => 3, 'driver_id' => 9,
            'service' => 'ride', 'service_class' => 'standard', 'pricing_style' => 'manual',
            'status' => 'assigned', 'pickup_lat' => 25.0, 'pickup_lng' => 51.0,
            'dropoff_lat' => 25.1, 'dropoff_lng' => 51.1, 'currency_code' => 'USD',
            'fare_minor' => 5000, 'total_minor' => 5000,
        ]);
        $this->bookingId = (int) $booking->id;
    }

    private int $bookingId;

    public function test_booking_channel_allows_only_ride_parties(): void
    {
        $this->assertTrue($this->auth->authorize('user', 5, 'booking.' . $this->bookingId));
        $this->assertTrue($this->auth->authorize('driver', 9, 'booking.' . $this->bookingId));
    }

    public function test_booking_channel_denies_non_parties(): void
    {
        $this->assertFalse($this->auth->authorize('user', 6, 'booking.' . $this->bookingId));
        $this->assertFalse($this->auth->authorize('driver', 3, 'booking.' . $this->bookingId));
    }

    public function test_unknown_booking_denied(): void
    {
        $this->assertFalse($this->auth->authorize('user', 5, 'booking.999999'));
    }
}
