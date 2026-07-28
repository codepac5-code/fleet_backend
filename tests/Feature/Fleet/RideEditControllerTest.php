<?php

namespace Tests\Feature\Fleet;

use App\Models\Office;
use App\Models\RideBooking;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;

/**
 * Coverage for RideEditController — mid-trip `change-route` + `add-stop`. These
 * two routes had ZERO tests. Both re-price the ride (pickup → stops → dropoff)
 * and are gated to editable statuses + the owning rider.
 *
 * Deterministic pricing trick: when the new dropoff equals the pickup and there
 * are no stops, the routed distance is 0, so the metered fare collapses to the
 * tariff's opening price alone — an exact, hand-checkable number.
 */
class RideEditControllerTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2024_10_29_211028_create_offices_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_16_000002_add_stops_to_ride_bookings.php',
    ];

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function office(): Office
    {
        return Office::query()->create([
            'officeName' => 'Al Fleet', 'email' => 'o@x.qa', 'password' => 'x',
            'contactNumber' => '33001234', 'address' => 'West Bay, Doha', 'country' => 'QA',
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1, 'is_verified' => true, 'lat' => 25.28, 'lng' => 51.53,
        ]);
    }

    private function booking(int $userId, int $officeId, string $status, array $extra = []): RideBooking
    {
        return RideBooking::query()->create(array_merge([
            'user_id' => $userId, 'office_id' => $officeId, 'source' => 'rider',
            'service' => 'ride', 'service_class' => 'standard', 'pricing_style' => 'meter',
            'status' => $status, 'pickup_lat' => 25.28, 'pickup_lng' => 51.53, 'pickup_title' => 'A',
            'dropoff_lat' => 25.27, 'dropoff_lng' => 51.60, 'dropoff_title' => 'B',
            'distance_m' => 5400, 'duration_s' => 720, 'currency_code' => 'USD',
            'fare_minor' => 5000, 'total_minor' => 5000, 'payment_method' => 'cash',
        ], $extra));
    }

    /** A metered tariff whose name matches the booking's service_class. */
    private function tariff(string $name = 'standard', float $open = 10, float $km = 2, float $min = 1): SubService
    {
        $service = Service::query()->create([
            'image' => 'x', 'status' => true, 'title' => 'Ride', 'title_en' => 'Ride', 'travel_service' => 0,
        ]);

        return SubService::query()->create([
            'name' => $name, 'name_en' => $name, 'status' => true,
            'openPrice' => $open, 'kmPrice' => $km, 'minutePrice' => $min,
            'serviceId' => $service->id, 'is_travel' => 0,
        ]);
    }

    // ── change-route ────────────────────────────────────────────────

    public function test_change_route_updates_dropoff_and_reprices_with_tariff(): void
    {
        $office = $this->office();
        $this->tariff('standard', open: 10, km: 2, min: 1);
        $b = $this->booking(7, $office->id, 'on_trip');

        // New dropoff == pickup → routed distance 0 → fare = openPrice (10.00).
        $this->asUser()->postJson("user/trips/{$b->id}/change-route", [
            'dropoff' => ['lat' => 25.28, 'lng' => 51.53, 'title' => 'Back to start'],
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.dropoff_lat', 25.28)
            ->assertJsonPath('data.dropoff_title', 'Back to start')
            ->assertJsonPath('data.distance_m', 0)
            ->assertJsonPath('data.duration_s', 0)
            ->assertJsonPath('data.fare_minor', 1000)
            ->assertJsonPath('data.total_minor', 1000);

        $fresh = $b->fresh();
        $this->assertEqualsWithDelta(25.28, (float) $fresh->dropoff_lat, 1e-9);
        $this->assertSame(0, (int) $fresh->distance_m);
    }

    public function test_change_route_without_a_tariff_updates_geometry_but_not_fare(): void
    {
        $office = $this->office();
        // service_class has no matching SubService → reprice leaves fare alone.
        $b = $this->booking(7, $office->id, 'on_trip', ['service_class' => 'no_such_class']);

        $this->asUser()->postJson("user/trips/{$b->id}/change-route", [
            'dropoff' => ['lat' => 25.28, 'lng' => 51.53],
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.distance_m', 0)
            ->assertJsonPath('data.fare_minor', 5000)   // unchanged
            ->assertJsonPath('data.total_minor', 5000);
    }

    public function test_change_route_recomputes_a_nonzero_distance_on_a_real_move(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'assigned');

        $res = $this->asUser()->postJson("user/trips/{$b->id}/change-route", [
            'dropoff' => ['lat' => 25.10, 'lng' => 51.40],
        ])->assertStatus(200);

        $distance = $res->json('data.distance_m');
        $this->assertIsInt($distance);
        $this->assertGreaterThan(0, $distance);
        $this->assertNotSame(5400, $distance); // was re-computed, not left stale
    }

    // ── add-stop ────────────────────────────────────────────────────

    public function test_add_stop_appends_and_returns_201(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'on_trip');

        $this->asUser()->postJson("user/trips/{$b->id}/add-stop", [
            'lat' => 25.29, 'lng' => 51.55, 'title' => 'Quick stop',
        ])
            ->assertStatus(201)
            ->assertJsonCount(1, 'data.stops')
            ->assertJsonPath('data.stops.0.title', 'Quick stop')
            ->assertJsonPath('data.stops.0.lat', 25.29)
            ->assertJsonPath('data.booking.id', $b->id);

        $this->assertCount(1, $b->fresh()->stops);
    }

    public function test_add_stop_accumulates_multiple_stops_in_order(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'on_trip');

        $this->asUser()->postJson("user/trips/{$b->id}/add-stop", ['lat' => 25.29, 'lng' => 51.55, 'title' => 'First'])
            ->assertStatus(201);
        $this->asUser()->postJson("user/trips/{$b->id}/add-stop", ['lat' => 25.30, 'lng' => 51.56, 'title' => 'Second'])
            ->assertStatus(201)
            ->assertJsonCount(2, 'data.stops')
            ->assertJsonPath('data.stops.0.title', 'First')
            ->assertJsonPath('data.stops.1.title', 'Second');
    }

    public function test_add_stop_title_is_optional(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'arrived');

        $this->asUser()->postJson("user/trips/{$b->id}/add-stop", ['lat' => 25.29, 'lng' => 51.55])
            ->assertStatus(201)
            ->assertJsonPath('data.stops.0.title', null);
    }

    // ── validation ──────────────────────────────────────────────────

    public function test_change_route_requires_a_valid_dropoff(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'on_trip');
        $u = $this->asUser();

        $u->postJson("user/trips/{$b->id}/change-route", [])
            ->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
        $u->postJson("user/trips/{$b->id}/change-route", ['dropoff' => ['lat' => 200, 'lng' => 51]])
            ->assertStatus(422);
        $u->postJson("user/trips/{$b->id}/change-route", ['dropoff' => ['lat' => 25, 'lng' => 900]])
            ->assertStatus(422);
    }

    public function test_add_stop_requires_finite_in_range_coords(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'on_trip');
        $u = $this->asUser();

        $u->postJson("user/trips/{$b->id}/add-stop", ['lng' => 51.5])->assertStatus(422); // no lat
        $u->postJson("user/trips/{$b->id}/add-stop", ['lat' => 25.5, 'lng' => 999])->assertStatus(422);
        $u->postJson("user/trips/{$b->id}/add-stop", ['lat' => 'x', 'lng' => 51.5])->assertStatus(422);
    }

    // ── authorization + state ───────────────────────────────────────

    public function test_cannot_edit_a_booking_you_do_not_own(): void
    {
        $office = $this->office();
        $b = $this->booking(7, $office->id, 'on_trip');

        $this->asUser(8)->postJson("user/trips/{$b->id}/change-route", [
            'dropoff' => ['lat' => 25.1, 'lng' => 51.1],
        ])->assertStatus(404);

        $this->asUser(8)->postJson("user/trips/{$b->id}/add-stop", ['lat' => 25.1, 'lng' => 51.1])
            ->assertStatus(404);
    }

    public function test_cannot_edit_a_completed_or_cancelled_ride(): void
    {
        $office = $this->office();
        $done = $this->booking(7, $office->id, 'completed');
        $cancelled = $this->booking(7, $office->id, 'cancelled');

        $this->asUser()->postJson("user/trips/{$done->id}/change-route", [
            'dropoff' => ['lat' => 25.1, 'lng' => 51.1],
        ])->assertStatus(409)->assertJsonPath('error.code', 'booking_not_editable');

        $this->asUser()->postJson("user/trips/{$cancelled->id}/add-stop", ['lat' => 25.1, 'lng' => 51.1])
            ->assertStatus(409);
    }

    public function test_editing_a_missing_booking_is_404(): void
    {
        $this->asUser()->postJson('user/trips/999999/change-route', [
            'dropoff' => ['lat' => 25.1, 'lng' => 51.1],
        ])->assertStatus(404);
    }

    public function test_unauthenticated_edit_is_401(): void
    {
        $this->postJson('user/trips/1/change-route', ['dropoff' => ['lat' => 25, 'lng' => 51]])
            ->assertStatus(401);
        $this->postJson('user/trips/1/add-stop', ['lat' => 25, 'lng' => 51])
            ->assertStatus(401);
    }
}
