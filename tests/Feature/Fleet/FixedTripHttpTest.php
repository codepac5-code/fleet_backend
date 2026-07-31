<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Models\Office;
use App\Models\ServiceTariff;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * The rider HTTP surface for fixed trips (offers / select / cancel). Proves the
 * routes, controller validation and Reply envelope on top of the fully
 * unit-tested FixedTripService.
 */
class FixedTripHttpTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2024_10_26_104402_create_services_table.php',
        '2024_10_26_104427_create_sub_services_table.php',
        '2024_11_13_070726_create_cities_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_16_000002_add_stops_to_ride_bookings.php',
        '2026_07_17_000003_add_arrived_at_to_ride_bookings.php',
        '2024_10_29_211028_create_offices_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2025_11_14_205812_create_travel_routes_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_20_000001_create_fixed_trip_meta_table.php',
        '2026_07_21_000001_add_corridor_to_fixed_trip_meta.php',
    ];

    private const SUB = 1;
    private const DEP = 1;
    private const ARR = 2;

    protected function setUp(): void
    {
        parent::setUp();
        app()->instance('shard_currency', 'SYP');
    }

    private function asUser(int $id = 7): self
    {
        $u = new User();
        $u->id = $id;

        return $this->actingAs($u, 'user');
    }

    private function seedFixture(): void
    {
        \App\Models\Service::query()->create(['id' => 1, 'image' => 'x.png', 'title' => 'Travel', 'title_en' => 'Travel', 'travel_service' => 1, 'status' => 1]);
        \App\Models\SubService::query()->create([
            'id' => self::SUB, 'name' => 'Travel Sedan', 'name_en' => 'Travel Sedan', 'serviceId' => 1,
            'openPrice' => 0, 'kmPrice' => 0, 'minutePrice' => 0, 'is_travel' => true, 'status' => 1,
        ]);
        \App\Models\City::query()->create(['id' => self::DEP, 'name' => 'دمشق', 'en_name' => 'Damascus', 'name_on_google_map' => 'Damascus', 'countryId' => 1]);
        \App\Models\City::query()->create(['id' => self::ARR, 'name' => 'حلب', 'en_name' => 'Aleppo', 'name_on_google_map' => 'Aleppo', 'countryId' => 1]);

        Office::query()->create([
            'id' => 1, 'officeName' => 'Damascus Luxury Fleet', 'email' => 'o1@x.sy', 'password' => 'x',
            'contactNumber' => '11', 'address' => 'Damascus', 'country' => 'SY', 'city' => 'Damascus',
            'region' => 'Midan', 'status' => 1, 'is_verified' => true, 'rating' => 4.6, 'lat' => 33.51, 'lng' => 36.29,
        ]);
        // Damascus → Aleppo corridor, flat 120.00 (→ 12,000 minor).
        \App\Models\TravelRoutes::query()->create([
            'officeId' => 1, 'sub_service_id' => self::SUB,
            'departure_city_id' => self::DEP, 'arrival_city_id' => self::ARR, 'trip_price' => 120,
        ]);
        (new FleetWalletService(new LedgerService()))->topUp(7, 50000, 'SYP', 'fund:7', 'test', 1);
    }

    private function selectBody(array $over = []): array
    {
        return array_merge([
            'office_id' => 1,
            'sub_service_id' => self::SUB,
            'departure_city_id' => self::DEP,
            'arrival_city_id' => self::ARR,
            'scheduled_at' => Carbon::now()->addDays(2)->toDateTimeString(),
            'payment_method' => 'wallet',
        ], $over);
    }

    public function test_travel_classes_are_listed_from_the_service_not_the_dead_column(): void
    {
        $this->seedFixture();

        // `sub_services.is_travel` is never set by anything; the authority is
        // `services.travel_service`. Filtering on the column returned nothing,
        // so the app's Airport & Travel page said "no service available" while
        // the travel classes sat published.
        \App\Models\Service::query()->create(['id' => 2, 'image' => 'x.png', 'title' => 'Ride', 'title_en' => 'Ride', 'travel_service' => 0, 'status' => 1]);
        // `sub_services.id` is not fillable, so keep what the DB assigned.
        $cityRide = \App\Models\SubService::query()->create([
            'name' => 'City Ride', 'name_en' => 'City Ride', 'serviceId' => 2,
            'openPrice' => 5, 'kmPrice' => 2, 'minutePrice' => 1, 'is_travel' => false, 'status' => 1,
        ]);
        $airport = \App\Models\SubService::query()->create([
            'name' => 'Airport Pickup', 'name_en' => 'Airport Pickup', 'serviceId' => 1,
            'openPrice' => 0, 'kmPrice' => 0, 'minutePrice' => 0, 'is_travel' => false, 'status' => 1,
        ]);

        $travel = $this->asUser()->getJson('user/fixed/sub-services?travel=1')
            ->assertStatus(200)
            ->json('data.sub_services');

        $ids = array_column($travel, 'id');
        $this->assertContains($airport->id, $ids, 'a travel-service class must be listed even with is_travel = 0');
        $this->assertNotContains($cityRide->id, $ids, 'a city ride class must not appear under travel');

        $city = $this->asUser()->getJson('user/fixed/sub-services?travel=0')
            ->assertStatus(200)
            ->json('data.sub_services');

        $this->assertContains($cityRide->id, array_column($city, 'id'));
        $this->assertNotContains($airport->id, array_column($city, 'id'));
    }

    public function test_offers_returns_priced_offices(): void
    {
        $this->seedFixture();

        $this->asUser()->postJson('user/fixed/offers', [
            'sub_service_id' => self::SUB,
            'departure_city_id' => self::DEP,
            'arrival_city_id' => self::ARR,
        ])->assertStatus(200)
            ->assertJsonPath('data.offers.0.office_id', 1)
            ->assertJsonPath('data.offers.0.fare_minor', 12000);
    }

    public function test_select_creates_a_pending_booking(): void
    {
        $this->seedFixture();

        $this->asUser()->postJson('user/fixed/select', $this->selectBody())
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending_acceptance')
            ->assertJsonPath('data.locked_fare_minor', 12000)
            ->assertJsonPath('data.held_minor', 12000)
            ->assertJsonPath('data.context', 'personal');
    }

    public function test_select_carries_the_corporate_context(): void
    {
        $this->seedFixture();

        $this->asUser()->postJson('user/fixed/select', $this->selectBody([
            'context' => 'corporate', 'company_id' => 42, 'payment_method' => 'cash',
        ]))->assertStatus(201)
            ->assertJsonPath('data.context', 'corporate');
    }

    public function test_select_validation_rejects_missing_corridor(): void
    {
        $this->seedFixture();

        // No sub_service / cities → the corridor is undefined.
        $this->asUser()->postJson('user/fixed/select', ['office_id' => 1])
            ->assertStatus(422);
    }

    public function test_select_rejects_a_corridor_the_office_does_not_serve(): void
    {
        $this->seedFixture();
        // City 3 exists as an id but the office publishes no Damascus → 3 route.
        \App\Models\City::query()->create(['id' => 3, 'name' => 'حمص', 'en_name' => 'Homs', 'name_on_google_map' => 'Homs', 'countryId' => 1]);

        $this->asUser()->postJson('user/fixed/select', $this->selectBody([
            'arrival_city_id' => 3,
        ]))->assertStatus(404); // route_not_available — no corridor = no offer
    }

    public function test_show_returns_the_trip_with_trip_type(): void
    {
        $this->seedFixture();
        $id = $this->asUser()->postJson('user/fixed/select', $this->selectBody())
            ->json('data.id');

        $this->asUser()->getJson("user/fixed/$id")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.trip_type', 'fixed')
            ->assertJsonPath('data.status', 'pending_acceptance')
            ->assertJsonPath('data.locked_fare_minor', 12000);
    }

    public function test_show_is_scoped_to_the_owner(): void
    {
        $this->seedFixture();
        $id = $this->asUser(7)->postJson('user/fixed/select', $this->selectBody())
            ->json('data.id');

        // A different rider must not be able to read someone else's trip.
        $this->asUser(99)->getJson("user/fixed/$id")->assertStatus(404);
    }

    public function test_cancel_outside_window_refunds_in_full(): void
    {
        $this->seedFixture();
        $id = $this->asUser()->postJson('user/fixed/select', $this->selectBody([
            'scheduled_at' => Carbon::now()->addHours(6)->toDateTimeString(),
        ]))->json('data.id');

        $this->asUser()->postJson("user/fixed/$id/cancel")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.cancellation_fee_minor', 0);
    }
}
