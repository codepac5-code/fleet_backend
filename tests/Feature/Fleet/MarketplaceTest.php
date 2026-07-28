<?php

namespace Tests\Feature\Fleet;

use App\Models\Office;
use App\Models\OfficeSubServicePrice;
use App\Models\RideBooking;
use App\Models\Service;
use App\Models\ServiceTariff;
use App\Models\SubService;
use App\Models\User;

/**
 * Rider-facing marketplace: what an office costs for THIS route, who is
 * nearest, what an office offers, and the favourite/rating loop.
 *
 * This file used to target `POST api/v1/trip-options` and
 * `POST user/offices/available`, neither of which exists any more. The live
 * surface is:
 *
 *   POST user/routes/estimate   priced class list for a route
 *   POST user/offices/search    office cards ranked for a route  (body: {route:{…}})
 *   GET  user/offices/{id}      office profile + services + classes
 *   POST user/trips/{id}/rating dual driver+office rating, optional favourite
 *   GET  user/me/favorites      favourited office CARDS (not bare ids)
 *
 * Two contract shifts worth pinning, because the old assertions encoded the
 * opposite:
 *  - the office catalogue is now Service / SubService / OfficeSubServicePrice.
 *    ServiceTariff still exists, but only as the PRICING engine layered on top:
 *    an office is *listed* because it sells the sub-service, and *priced*
 *    because a tariff matches. No tariff => a card with no `fare_minor`, not a
 *    missing card.
 *  - office search ranks by distance from the pickup, not by driver supply.
 *    DriverPresence no longer participates in this endpoint at all.
 */
class MarketplaceTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2026_01_03_025343_create_office_sub_service_prices_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private const AIRPORT = [25.2731, 51.6080];
    private const CITY = [25.2854, 51.5310];

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function office(string $name, float $lat, float $lng): Office
    {
        return Office::query()->create([
            'officeName' => $name, 'email' => strtolower($name) . '@x.qa', 'password' => 'x',
            'contactNumber' => '33001234', 'address' => 'Doha', 'country' => 'QA',
            'city' => 'Doha', 'region' => 'Doha', 'status' => 1, 'is_verified' => true,
            'lat' => $lat, 'lng' => $lng,
        ]);
    }

    /** A service + one class, sold by $office. Returns [Service, SubService]. */
    private function catalog(Office $office, string $service = 'Travel', string $class = 'Standard'): array
    {
        $svc = Service::query()->create([
            'title' => $service, 'title_en' => $service, 'image' => 'svc.png',
            'status' => 1, 'travel_service' => true,
        ]);

        $sub = SubService::query()->create([
            'name' => $class, 'name_en' => $class, 'serviceId' => $svc->id, 'status' => 1,
            'is_travel' => true, 'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1,
        ]);

        OfficeSubServicePrice::query()->create([
            'office_id' => $office->id, 'sub_service_id' => $sub->id,
            'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1,
        ]);

        return [$svc, $sub];
    }

    /** Also list $office under an existing class (so two offices sell the same one). */
    private function sell(Office $office, SubService $sub): void
    {
        OfficeSubServicePrice::query()->create([
            'office_id' => $office->id, 'sub_service_id' => $sub->id,
            'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1,
        ]);
    }

    /**
     * The tariff is keyed by the SUB-SERVICE ID, because that is the
     * `serviceClass` the app sends. `service` is left null so the resolver's
     * `service = ? OR service IS NULL` branch matches whatever id is passed.
     */
    private function fixedTariff(int $officeId, int $subId, int $fixed): void
    {
        ServiceTariff::query()->create([
            'office_id' => $officeId, 'service' => null, 'service_class' => (string) $subId,
            'currency_code' => 'USD', 'pricing_style' => 'fixed', 'fixed_minor' => $fixed,
        ]);
    }

    private function searchBody(array $route = []): array
    {
        return ['route' => array_merge([
            'pickup' => ['lat' => self::CITY[0], 'lng' => self::CITY[1]],
            'dropoff' => ['lat' => self::AIRPORT[0], 'lng' => self::AIRPORT[1]],
        ], $route)];
    }

    // ── route estimate (was: POST api/v1/trip-options) ──────────────────────

    /**
     * The class list a rider sees before picking an office. Preserves the old
     * "lists classes with a real fare" intent; the airport/route-type detection
     * the old endpoint did has no live counterpart.
     */
    public function test_route_estimate_prices_every_active_class(): void
    {
        $office = $this->office('Alfa', self::CITY[0], self::CITY[1]);
        $this->catalog($office);

        $res = $this->asUser()->postJson('user/routes/estimate', [
            'pickup' => ['lat' => self::CITY[0], 'lng' => self::CITY[1]],
            'dropoff' => ['lat' => self::AIRPORT[0], 'lng' => self::AIRPORT[1]],
        ])->assertStatus(200);

        $this->assertGreaterThan(0, $res->json('data.distance_m'));
        $this->assertGreaterThan(0, $res->json('data.duration_s'));
        $this->assertNotEmpty($res->json('data.polyline'));

        $standard = collect($res->json('data.classes'))->firstWhere('name', 'Standard');
        $this->assertNotNull($standard);
        // JSON collapses the float 5.0 to 5, so compare loosely on the value.
        $this->assertEquals(5, $standard['base_fare']);
        $this->assertSame('USD', $standard['currency_code']);
        // openPrice + kmPrice*km + minutePrice*min, in minor units.
        $this->assertGreaterThan(500, $standard['fare_minor']);
    }

    /** Fare is distance-driven: a longer route must not cost the same. */
    public function test_route_estimate_fare_scales_with_distance(): void
    {
        $office = $this->office('Alfa', self::CITY[0], self::CITY[1]);
        $this->catalog($office);

        $short = $this->asUser()->postJson('user/routes/estimate', [
            'pickup' => ['lat' => self::CITY[0], 'lng' => self::CITY[1]],
            'dropoff' => ['lat' => self::CITY[0] + 0.01, 'lng' => self::CITY[1]],
        ])->assertStatus(200);

        $long = $this->asUser()->postJson('user/routes/estimate', [
            'pickup' => ['lat' => self::CITY[0], 'lng' => self::CITY[1]],
            'dropoff' => ['lat' => self::AIRPORT[0], 'lng' => self::AIRPORT[1]],
        ])->assertStatus(200);

        $this->assertGreaterThan(
            $short->json('data.classes.0.fare_minor'),
            $long->json('data.classes.0.fare_minor')
        );
    }

    /**
     * Was `invalid_service` — the live endpoint takes no service, so the
     * equivalent rejection is on the coordinates. EstimateRequest bounds both.
     */
    public function test_route_estimate_rejects_out_of_range_coordinates(): void
    {
        $this->asUser()->postJson('user/routes/estimate', [
            'pickup' => ['lat' => 999, 'lng' => 51.53],
            'dropoff' => ['lat' => self::AIRPORT[0], 'lng' => self::AIRPORT[1]],
        ])->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');

        $this->asUser()->postJson('user/routes/estimate', [
            'pickup' => ['lat' => self::CITY[0], 'lng' => self::CITY[1]],
        ])->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
    }

    // ── office search (was: POST user/offices/available) ────────────────────

    /**
     * Ranking is by drive distance from the PICKUP — the nearest office leads,
     * regardless of price. (The old endpoint ranked by driver supply; that
     * concept is gone from this route.)
     */
    public function test_offices_search_ranks_nearest_pickup_first(): void
    {
        $near = $this->office('Near', self::CITY[0], self::CITY[1]);
        $far = $this->office('Far', self::AIRPORT[0], self::AIRPORT[1]);

        [$svc, $sub] = $this->catalog($near);
        $this->sell($far, $sub);

        $res = $this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => $svc->id, 'serviceClass' => $sub->id,
        ]))->assertStatus(200);

        $offices = $res->json('data.offices');
        $this->assertCount(2, $offices);
        $this->assertSame($near->id, $offices[0]['id']);
        $this->assertSame($far->id, $offices[1]['id']);

        // ETA is the office's own drive to the pickup, so the co-located office
        // clamps to the 1-minute floor and the airport office is strictly worse.
        $this->assertSame(1, $offices[0]['eta_minutes']);
        $this->assertGreaterThan(1, $offices[1]['eta_minutes']);
    }

    /**
     * The card carries the fare the rider will actually be charged, quoted
     * through the same tariff engine the booking pipeline uses — and an office
     * with no tariff is still LISTED, just unpriced. That asymmetry is the part
     * that is easy to break.
     */
    public function test_offices_search_prices_cards_from_the_tariff_engine(): void
    {
        $priced = $this->office('Priced', self::CITY[0], self::CITY[1]);
        $unpriced = $this->office('Unpriced', self::CITY[0] + 0.5, self::CITY[1]);

        [$svc, $sub] = $this->catalog($priced);
        $this->sell($unpriced, $sub);
        $this->fixedTariff($priced->id, $sub->id, 5000);

        $offices = collect($this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => $svc->id, 'serviceClass' => $sub->id,
        ]))->assertStatus(200)->json('data.offices'));

        $card = $offices->firstWhere('id', $priced->id);
        $this->assertSame(5000, $card['fare_minor']);
        $this->assertSame('fixed', $card['pricing_style']);
        $this->assertSame('USD', $card['currency_code']);

        $this->assertArrayNotHasKey('fare_minor', $offices->firstWhere('id', $unpriced->id));
    }

    /** With `route.meter`, cards are priced on the meter (sub-service), not tariff. */
    public function test_offices_search_prices_cards_on_the_meter_when_flagged(): void
    {
        app()->instance('shard_currency', 'USD');
        $office = $this->office('Meter Co', self::CITY[0], self::CITY[1]);
        [$svc, $sub] = $this->catalog($office);
        // A tariff exists, but the meter flag must override it with meter pricing.
        $this->fixedTariff($office->id, $sub->id, 5000);

        $card = collect($this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => '', 'serviceClass' => $sub->id, 'meter' => true,
        ]))->assertStatus(200)->json('data.offices'))->firstWhere('id', $office->id);

        $this->assertSame('meter', $card['pricing_style']);
        $this->assertNotSame(5000, $card['fare_minor'], 'meter fare must differ from the flat tariff');
        $this->assertGreaterThan(0, $card['fare_minor']);
    }

    /** Only offices that actually sell the requested class come back. */
    public function test_offices_search_excludes_offices_without_the_class(): void
    {
        $seller = $this->office('Seller', self::CITY[0], self::CITY[1]);
        $this->office('Bystander', self::CITY[0], self::CITY[1]);

        [$svc, $sub] = $this->catalog($seller);

        $offices = $this->asUser()->postJson('user/offices/search', $this->searchBody([
            'service' => $svc->id, 'serviceClass' => $sub->id,
        ]))->assertStatus(200)->json('data.offices');

        $this->assertCount(1, $offices);
        $this->assertSame($seller->id, $offices[0]['id']);
    }

    public function test_offices_search_rejects_bad_coordinates(): void
    {
        $this->asUser()->postJson('user/offices/search', $this->searchBody([
            'pickup' => ['lat' => 25.28, 'lng' => 999],
        ]))->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_offices_search_requires_a_route(): void
    {
        $this->asUser()->postJson('user/offices/search', [])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    // ── office profile ──────────────────────────────────────────────────────

    public function test_office_profile_lists_services_and_classes(): void
    {
        $office = $this->office('Alfa', self::CITY[0], self::CITY[1]);
        [$svc, $sub] = $this->catalog($office, 'Travel', 'Standard');

        $res = $this->asUser()->getJson("user/offices/{$office->id}")
            ->assertStatus(200)
            // the card key is `id`, not the old `office_id`
            ->assertJsonPath('data.id', $office->id)
            ->assertJsonPath('data.officeName', 'Alfa');

        $this->assertContains('Travel', collect($res->json('data.services'))->pluck('title')->all());
        $this->assertContains('Standard', collect($res->json('data.classes'))->pluck('name')->all());
    }

    public function test_office_profile_unknown_is_404(): void
    {
        $this->asUser()->getJson('user/offices/424242')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'office_not_found');
    }

    // ── rating + favourites ─────────────────────────────────────────────────

    /**
     * One rating call writes BOTH sides. With no assigned driver only the office
     * row is written — the driver row is conditional on the dispatch assignment,
     * which is exactly the coexistence the old `office-rating` test pinned.
     */
    public function test_rating_writes_office_row_and_favourites_the_office(): void
    {
        $office = $this->office('Alfa', self::CITY[0], self::CITY[1]);

        $booking = RideBooking::query()->create([
            'user_id' => 7, 'office_id' => $office->id, 'service' => 'travel', 'service_class' => 'standard',
            'pricing_style' => 'fixed', 'status' => 'completed',
            'pickup_lat' => self::CITY[0], 'pickup_lng' => self::CITY[1],
            'dropoff_lat' => self::AIRPORT[0], 'dropoff_lng' => self::AIRPORT[1],
            'currency_code' => 'USD', 'fare_minor' => 5000, 'total_minor' => 5000,
        ]);

        $this->asUser(7)->postJson("user/trips/{$booking->id}/rating", ['stars' => 4, 'favorite' => true])
            ->assertStatus(200)
            ->assertJsonPath('data.ok', true);

        $this->assertDatabaseHas('ride_ratings', [
            'booking_id' => $booking->id, 'ratee_type' => 'office', 'ratee_id' => $office->id, 'stars' => 4,
        ]);

        // Favourites now return full office CARDS, not a bare `office_ids` list.
        $this->asUser(7)->getJson('user/me/favorites')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $office->id)
            ->assertJsonPath('data.0.officeName', 'Alfa');
    }

    public function test_rating_a_foreign_booking_is_404(): void
    {
        $office = $this->office('Alfa', self::CITY[0], self::CITY[1]);

        $booking = RideBooking::query()->create([
            'user_id' => 7, 'office_id' => $office->id, 'service' => 'travel', 'service_class' => 'standard',
            'pricing_style' => 'fixed', 'status' => 'completed',
            'pickup_lat' => self::CITY[0], 'pickup_lng' => self::CITY[1],
            'dropoff_lat' => self::AIRPORT[0], 'dropoff_lng' => self::AIRPORT[1],
            'currency_code' => 'USD', 'fare_minor' => 5000, 'total_minor' => 5000,
        ]);

        $this->asUser(8)->postJson("user/trips/{$booking->id}/rating", ['stars' => 4])
            ->assertStatus(404);

        $this->assertDatabaseMissing('ride_ratings', ['booking_id' => $booking->id]);
    }

    /** Favourites are per-rider: user B's list never leaks user A's offices. */
    public function test_favourites_are_scoped_to_the_caller(): void
    {
        $office = $this->office('Alfa', self::CITY[0], self::CITY[1]);

        $this->asUser(7)->postJson("user/me/favorites/{$office->id}")->assertStatus(204);

        $this->asUser(7)->getJson('user/me/favorites')->assertStatus(200)->assertJsonCount(1, 'data');
        $this->asUser(8)->getJson('user/me/favorites')->assertStatus(200)->assertJsonCount(0, 'data');

        $this->asUser(7)->deleteJson("user/me/favorites/{$office->id}")->assertStatus(204);
        $this->asUser(7)->getJson('user/me/favorites')->assertStatus(200)->assertJsonCount(0, 'data');
    }
}
