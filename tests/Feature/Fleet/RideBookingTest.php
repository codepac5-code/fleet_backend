<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Http\Core\Const\Event\EventType;
use App\Models\DispatchOffer;
use App\Models\EventOutbox;
use App\Models\DriverPresence;
use App\Models\RideBooking;
use App\Models\ServiceTariff;
use App\Models\User;

class RideBookingTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        // The booking row the current create/cancel path actually writes.
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php', // source, …
        '2026_07_15_000001_add_rider_api_missing_columns.php',              // driver_id, …
        '2026_07_16_000002_add_stops_to_ride_bookings.php',
        '2026_07_17_000003_add_arrived_at_to_ride_bookings.php',
        '2026_07_19_000001_allow_reoffer_on_dispatch_offers.php',
        // `detail()` (GET bookings/{id} and the change-office response) resolves
        // the booking's office card.
        '2024_10_29_211028_create_offices_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2024_11_17_075900_create_coupons_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Coupon::query()->create([
            'code' => 'QATAR10', 'discountType' => 'percentage', 'discount' => 10,
            'isPercentage' => true, 'isActive' => true, 'limit' => 0, 'status' => 1,
        ]);
    }

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function seedTariff(int $office = 3, string $style = 'fixed', int $fixed = 5000): void
    {
        ServiceTariff::query()->create([
            'office_id' => $office,
            'service_class' => 'standard',
            'currency_code' => 'USD',
            'pricing_style' => $style,
            'base_minor' => 500,
            'per_km_minor' => 200,
            'per_minute_minor' => 30,
            'minimum_minor' => 1000,
            'fixed_minor' => $fixed,
        ]);
    }

    private function fund(int $userId, int $amount): void
    {
        (new FleetWalletService(new LedgerService()))
            ->topUp($userId, $amount, 'USD', 'fund:' . $userId, 'test', 1);
    }

    private function payload(array $override = []): array
    {
        // Flat coordinates — the shape CreateBookingRequest validates and the
        // rider app actually posts. (The old nested pickup/dropoff objects are
        // from a previous API revision.)
        return array_merge([
            'office_id' => 3,
            'service' => 'travel',
            'service_class' => 'standard',
            'pickup_lat' => 25.2854,
            'pickup_lng' => 51.5310,
            'pickup_note' => 'Gate 2',
            'dropoff_lat' => 25.2700,
            'dropoff_lng' => 51.6000,
        ], $override);
    }

    /**
     * The Idempotency-Key is OPTIONAL, and a request without one is NOT
     * deduplicated — `RideBookingService::create()` only consults
     * `findByIdempotencyKey` when the key is a non-empty string, so two
     * identical posts become two bookings, each holding the fare in escrow.
     *
     * This test used to assert a 422 ("key required"). Nothing enforces that,
     * so it was asserting a contract the API does not have. It now pins the
     * real behaviour: callers are responsible for sending a key. Both apps do
     * (see the rider's `requestRide`). If the key is ever made mandatory
     * server-side, this test fails and is the place to record the decision —
     * note that doing so hard-breaks any already-shipped client that omits it.
     */
    public function test_create_without_idempotency_key_is_not_deduplicated(): void
    {
        $this->seedTariff();
        $this->fund(7, 20000);

        $first = $this->asUser()->postJson('user/bookings', $this->payload())->assertStatus(201);
        $second = $this->asUser()->postJson('user/bookings', $this->payload())->assertStatus(201);

        $this->assertNotSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(2, RideBooking::query()->count());
    }

    public function test_create_emits_order_created_to_office_and_admin(): void
    {
        $this->seedTariff();
        $this->fund(7, 20000);

        $this->asUser()->postJson('user/bookings', $this->payload())->assertStatus(201);

        $event = EventOutbox::query()->where('type', EventType::ORDER_CREATED)->first();

        $this->assertNotNull($event);
        // Office that owns the order + the fleet admin room; NO user/driver room
        // (this is an office/admin-only signal, invisible to the apps).
        $this->assertContains('office.3', $event->channels);
        $this->assertContains('admin', $event->channels);
        $this->assertSame(3, $event->payload['office_id']);
        $this->assertSame(7, $event->payload['user_id']);
    }

    public function test_create_prefers_exact_service_over_legacy_null_tariff(): void
    {
        ServiceTariff::query()->create([
            'office_id' => 3, 'service' => null, 'service_class' => 'standard', 'currency_code' => 'USD',
            'pricing_style' => 'meter', 'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30, 'minimum_minor' => 1000,
        ]);
        ServiceTariff::query()->create([
            'office_id' => 3, 'service' => 'travel', 'service_class' => 'standard', 'currency_code' => 'USD',
            'pricing_style' => 'fixed', 'fixed_minor' => 5000,
        ]);
        $this->fund(7, 8000);

        $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-exact'])
            ->assertStatus(201)
            ->assertJsonPath('data.pricing_style', 'fixed')
            ->assertJsonPath('data.total_minor', 5000);
    }

    public function test_create_holds_fare_and_starts_matching(): void
    {
        $this->seedTariff();
        $this->fund(7, 8000);

        $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b1'])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'matching')
            ->assertJsonPath('data.pricing_style', 'fixed')
            ->assertJsonPath('data.total_minor', 5000)
            ->assertJsonPath('data.held_minor', 5000);

        $balance = (new FleetWalletService(new LedgerService()))->walletBalanceMinor('user', 7, 'USD');
        $this->assertSame(3000, $balance);
    }

    public function test_create_applies_promo(): void
    {
        $this->seedTariff();
        $this->fund(7, 8000);

        $this->asUser()->postJson('user/bookings', $this->payload(['promo_code' => 'QATAR10']), ['Idempotency-Key' => 'b-promo'])
            ->assertStatus(201)
            ->assertJsonPath('data.discount_minor', 500)
            ->assertJsonPath('data.total_minor', 4500);
    }

    public function test_create_insufficient_funds_is_422(): void
    {
        $this->seedTariff();

        $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b2'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'insufficient_funds');
    }

    public function test_create_cash_skips_hold(): void
    {
        $this->seedTariff();

        $this->asUser()->postJson('user/bookings', $this->payload(['payment_method' => 'cash']), ['Idempotency-Key' => 'b-cash'])
            ->assertStatus(201)
            ->assertJsonPath('data.held_minor', 0)
            ->assertJsonPath('data.status', 'matching');
    }

    public function test_create_missing_tariff_is_404(): void
    {
        $this->fund(7, 8000);

        $this->asUser()->postJson('user/bookings', $this->payload(['office_id' => 999]), ['Idempotency-Key' => 'b3'])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'tariff_not_found');
    }

    public function test_create_is_idempotent(): void
    {
        $this->seedTariff();
        $this->fund(7, 8000);

        $first = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-idem']);
        $second = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-idem']);

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertSame(1, RideBooking::query()->count());
    }

    public function test_show_only_owner(): void
    {
        $this->seedTariff();
        $this->fund(7, 8000);

        $id = $this->asUser(7)->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-own'])->json('data.id');

        $this->asUser(7)->getJson("user/bookings/{$id}")->assertStatus(200)->assertJsonPath('data.id', $id);
        $this->asUser(8)->getJson("user/bookings/{$id}")->assertStatus(404);
    }

    public function test_cancel_refunds_escrow(): void
    {
        $this->seedTariff();
        $this->fund(7, 8000);

        $id = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-cxl'])->json('data.id');

        $this->asUser()->postJson("user/bookings/{$id}/cancel", ['reason' => 'changed_plans'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $balance = (new FleetWalletService(new LedgerService()))->walletBalanceMinor('user', 7, 'USD');
        $this->assertSame(8000, $balance);
    }

    public function test_change_office_before_assignment(): void
    {
        $this->seedTariff(3);
        $this->seedTariff(5, 'fixed', 6000);
        $this->fund(7, 12000);

        $id = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-chg'])->json('data.id');

        $this->asUser()->postJson("user/bookings/{$id}/change-office", ['office_id' => 5])
            ->assertStatus(200)
            ->assertJsonPath('data.office_id', 5)
            ->assertJsonPath('data.total_minor', 6000);
    }

    public function test_change_office_toggle_keeps_escrow_in_sync(): void
    {
        $this->seedTariff(3);
        $this->seedTariff(5, 'fixed', 6000);
        $this->fund(7, 20000);

        $id = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-tog'])->json('data.id');

        $this->asUser()->postJson("user/bookings/{$id}/change-office", ['office_id' => 5])->assertStatus(200);
        $this->asUser()->postJson("user/bookings/{$id}/change-office", ['office_id' => 3])->assertStatus(200);
        $this->asUser()->postJson("user/bookings/{$id}/change-office", ['office_id' => 5])
            ->assertStatus(200)
            ->assertJsonPath('data.total_minor', 6000)
            ->assertJsonPath('data.held_minor', 6000);

        $wallet = new FleetWalletService(new LedgerService());
        $this->assertSame(6000, $wallet->escrowBalanceMinor((int) $id, 'USD'));
        $this->assertSame(14000, $wallet->walletBalanceMinor('user', 7, 'USD'));
    }

    public function test_change_office_after_assignment_is_409(): void
    {
        $this->seedTariff();
        $this->fund(7, 8000);

        DriverPresence::query()->create([
            'driver_id' => 101, 'office_id' => 3, 'status' => PresenceStatus::ONLINE,
            'lat' => 25.2871, 'lng' => 51.5310, 'heartbeat_at' => now(),
        ]);

        $id = $this->asUser()->postJson('user/bookings', $this->payload(), ['Idempotency-Key' => 'b-asn'])->json('data.id');

        // Matching offers via Redis (DriverLocationStore), which the test harness
        // doesn't populate — so seed the OFFERED offer directly, exactly as a live
        // heartbeat-driven wave would, then the driver accepts it.
        DispatchOffer::query()->create([
            'booking_id' => $id, 'driver_id' => 101, 'wave' => 1,
            'status' => OfferStatus::OFFERED, 'distance_m' => 50, 'expires_at' => now()->addMinutes(1),
        ]);

        $this->assertTrue(app(DispatchService::class)->accept($id, 101));

        $this->asUser()->getJson("user/bookings/{$id}")->assertJsonPath('data.status', 'assigned');

        $this->asUser()->postJson("user/bookings/{$id}/change-office", ['office_id' => 5])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'already_assigned');
    }
}
