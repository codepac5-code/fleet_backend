<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ride\FixedTripService;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Models\EventOutbox;
use App\Models\Office;
use App\Models\RideBooking;
use App\Models\ServiceTariff;
use Illuminate\Support\Carbon;

/**
 * The office-mediated fixed-trip state machine + every edge case from the spec.
 * Exercises the service directly (fast, no HTTP); the endpoints are thin
 * wrappers over these same calls and are covered separately.
 */
class FixedTripServiceTest extends FleetTestCase
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
        // Corridor catalog: services → sub_services, cities, and the travel_routes
        // that carry each office's flat corridor price.
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
        // Offices must exist BEFORE add_rider_api_missing_columns, which alters
        // it (is_verified, currency_code) that the corridor offers read.
        '2024_10_29_211028_create_offices_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        // travel_routes FKs cities + sub_services + offices — all created above.
        '2025_11_14_205812_create_travel_routes_table.php',
        // The offers summary averages an office's ride_ratings.
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_20_000001_create_fixed_trip_meta_table.php',
        '2026_07_21_000001_add_corridor_to_fixed_trip_meta.php',
    ];

    private FixedTripService $svc;

    // Corridor catalog shared by every test: sub-service #1 (travel), city 1
    // (Damascus) → city 2 (Aleppo).
    private const SUB = 1;
    private const DEP = 1;
    private const ARR = 2;

    protected function setUp(): void
    {
        parent::setUp();
        // The corridor currency is the tenant/shard currency (SY → SYP).
        app()->instance('shard_currency', 'SYP');
        $this->svc = app(FixedTripService::class);
        $this->seedCatalog();
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function seedCatalog(): void
    {
        \App\Models\Service::query()->create(['id' => 1, 'image' => 'x.png', 'title' => 'Travel', 'title_en' => 'Travel', 'travel_service' => 1, 'status' => 1]);
        \App\Models\SubService::query()->create([
            'id' => self::SUB, 'name' => 'Travel Sedan', 'name_en' => 'Travel Sedan', 'serviceId' => 1,
            'openPrice' => 0, 'kmPrice' => 0, 'minutePrice' => 0, 'is_travel' => true, 'status' => 1,
        ]);
        \App\Models\City::query()->create(['id' => self::DEP, 'name' => 'دمشق', 'en_name' => 'Damascus', 'name_on_google_map' => 'Damascus', 'countryId' => 1]);
        \App\Models\City::query()->create(['id' => self::ARR, 'name' => 'حلب', 'en_name' => 'Aleppo', 'name_on_google_map' => 'Aleppo', 'countryId' => 1]);
    }

    private function office(int $id, string $name, float $rating = 4.5): void
    {
        Office::query()->create([
            'id' => $id, 'officeName' => $name, 'email' => "o$id@x.sy", 'password' => 'x',
            'contactNumber' => '11', 'address' => 'Damascus', 'country' => 'SY', 'city' => 'Damascus',
            'region' => 'Midan', 'status' => 1, 'is_verified' => true, 'rating' => $rating,
            'lat' => 33.51, 'lng' => 36.29,
        ]);
    }

    /**
     * An office's flat corridor price for the shared Damascus → Aleppo route.
     * `$fareMinor` is given in MINOR units so the tests read the same as before;
     * travel_routes.trip_price is major, so we store `$fareMinor / 100`.
     */
    private function tariff(int $office, int $fareMinor): void
    {
        \App\Models\TravelRoutes::query()->create([
            'officeId' => $office,
            'sub_service_id' => self::SUB,
            'departure_city_id' => self::DEP,
            'arrival_city_id' => self::ARR,
            'trip_price' => $fareMinor / 100,
        ]);
    }

    private function wallet(): FleetWalletService
    {
        return new FleetWalletService(new LedgerService());
    }

    private function fund(int $userId, int $amount): void
    {
        $this->wallet()->topUp($userId, $amount, 'SYP', 'fund:' . $userId, 'test', 1);
    }

    private function selectPayload(int $officeId, array $over = []): array
    {
        return array_merge([
            'office_id' => $officeId,
            'sub_service_id' => self::SUB,
            'departure_city_id' => self::DEP,
            'arrival_city_id' => self::ARR,
            'scheduled_at' => Carbon::now()->addDays(2)->toDateTimeString(),
            'payment_method' => 'wallet',
            'pickup' => ['lat' => 33.5138, 'lng' => 36.2765, 'title' => 'Umayyad Sq'],
            'dropoff' => ['lat' => 33.4900, 'lng' => 36.3300, 'title' => 'Airport'],
        ], $over);
    }

    // ── happy path ───────────────────────────────────────────────────

    public function test_select_creates_pending_acceptance_and_holds_the_fare(): void
    {
        $this->office(1, 'Damascus Luxury Fleet');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);

        $res = $this->svc->select(7, $this->selectPayload(1));

        $this->assertSame(BookingStatus::PENDING_ACCEPTANCE, $res['status']);
        $this->assertSame(12000, $res['locked_fare_minor']);
        $this->assertSame(12000, $res['held_minor'], 'wallet fare must be held (authorized) at select');
        // Escrow really moved.
        $this->assertSame(12000, $this->wallet()->escrowBalanceMinor($res['id'], 'SYP'));
        $this->assertSame(38000, $this->wallet()->walletBalanceMinor('user', 7, 'SYP'));
    }

    public function test_office_accept_confirms_and_keeps_the_hold(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1))['id'];

        $res = $this->svc->accept(1, $id);

        $this->assertSame(BookingStatus::CONFIRMED, $res['status']);
        $this->assertNotNull($res['accepted_at']);
        // Capture-at-completion model: money stays held after accept.
        $this->assertSame(12000, $this->wallet()->escrowBalanceMinor($id, 'SYP'));
    }

    public function test_office_assigns_driver_moves_to_assigned(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1))['id'];
        $this->svc->accept(1, $id);

        $res = $this->svc->assignDriver(1, $id, 33);

        $this->assertSame(BookingStatus::ASSIGNED, $res['status']);
        $this->assertSame(33, $res['driver_id']);
    }

    // ── edge case: decline → next office at same-or-better fare ──────

    public function test_decline_reoffers_next_cheaper_office_and_honors_the_fare(): void
    {
        $this->office(1, 'Pricey', 4.9);
        $this->office(2, 'Cheaper', 4.2);
        $this->tariff(1, 12000);
        $this->tariff(2, 9000); // cheaper → qualifies as same-or-better
        $this->fund(7, 50000);

        $id = $this->svc->select(7, $this->selectPayload(1))['id'];
        $this->assertSame(1, RideBooking::query()->find($id)->office_id);

        $res = $this->svc->decline(1, $id, 'busy');

        // Re-routed to office 2, still pending, hold moved (not refunded away).
        $this->assertSame(BookingStatus::PENDING_ACCEPTANCE, $res['status']);
        $this->assertSame(2, $res['office_id']);
        $this->assertSame(12000, $this->wallet()->escrowBalanceMinor($id, 'SYP'),
            'the honored (locked) fare stays held on the backup office');
    }

    public function test_decline_with_no_backup_refunds_and_marks_declined(): void
    {
        $this->office(1, 'Only office');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1))['id'];

        $res = $this->svc->decline(1, $id, 'busy');

        $this->assertSame(BookingStatus::DECLINED, $res['status']);
        $this->assertSame(0, $this->wallet()->escrowBalanceMinor($id, 'SYP'), 'hold refunded on decline');
        $this->assertSame(50000, $this->wallet()->walletBalanceMinor('user', 7, 'SYP'));
    }

    public function test_a_more_expensive_backup_is_not_offered(): void
    {
        $this->office(1, 'First');
        $this->office(2, 'Dearer');
        $this->tariff(1, 9000);
        $this->tariff(2, 15000); // dearer than the locked 9000 → must NOT be used
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1))['id'];

        $res = $this->svc->decline(1, $id, 'busy');

        $this->assertSame(BookingStatus::DECLINED, $res['status'],
            'a rider is never re-routed to a more expensive office');
    }

    // ── edge case: cancellation policy (server-side) ─────────────────

    public function test_cancel_is_free_outside_the_window(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1, [
            'scheduled_at' => Carbon::now()->addHours(6)->toDateTimeString(),
        ]))['id'];

        $res = $this->svc->cancel(7, $id);

        $this->assertSame(BookingStatus::CANCELLED, $res['status']);
        $this->assertSame(0, $res['cancellation_fee_minor']);
        $this->assertSame(50000, $this->wallet()->walletBalanceMinor('user', 7, 'SYP'), 'full refund');
    }

    public function test_cancel_inside_the_window_charges_a_fee(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1, [
            'scheduled_at' => Carbon::now()->addMinutes(30)->toDateTimeString(), // inside 2h
        ]))['id'];

        $res = $this->svc->cancel(7, $id);

        $this->assertSame(BookingStatus::CANCELLED, $res['status']);
        $this->assertSame(1200, $res['cancellation_fee_minor'], '10% of the locked fare');
    }

    // ── edge case: SLA — no driver assigned → expired + refunded ─────

    public function test_sla_expiry_refunds_and_marks_no_driver_expired(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        // Pickup already inside the SLA window so sla_assign_by is in the past.
        $id = $this->svc->select(7, $this->selectPayload(1, [
            'scheduled_at' => Carbon::now()->addMinutes(10)->toDateTimeString(),
        ]))['id'];
        $this->svc->accept(1, $id);

        $count = $this->svc->expireOverdueAssignments();

        $this->assertSame(1, $count);
        $this->assertSame(BookingStatus::NO_DRIVER_EXPIRED, RideBooking::query()->find($id)->status);
        $this->assertSame(0, $this->wallet()->escrowBalanceMinor($id, 'SYP'), 'refunded on expiry');
    }

    // ── edge case: cash books nothing held ───────────────────────────

    public function test_cash_holds_nothing(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $id = $this->svc->select(7, $this->selectPayload(1, ['payment_method' => 'cash']))['id'];

        $this->assertSame(0, RideBooking::query()->find($id)->held_minor);
        $this->assertSame(0, $this->wallet()->escrowBalanceMinor($id, 'SYP'));
    }

    // ── edge case: wallet too low is 422, not a 500 ──────────────────

    public function test_insufficient_wallet_balance_is_rejected(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 5000); // < 12000

        $this->expectExceptionMessage('insufficient balance');
        $this->svc->select(7, $this->selectPayload(1));
    }

    // ── guard: can't accept an already-confirmed trip ────────────────

    public function test_accept_twice_is_an_invalid_transition(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1))['id'];
        $this->svc->accept(1, $id);

        $this->expectExceptionMessage('invalid transition');
        $this->svc->accept(1, $id);
    }

    // ── guard: the locked fare survives to the booking row ───────────

    public function test_locked_fare_is_written_to_the_booking_and_survives(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1))['id'];
        $this->svc->accept(1, $id);

        // No meter ticks ever happen on a fixed trip — the fare must still be here.
        $booking = RideBooking::query()->find($id);
        $this->assertSame(12000, (int) $booking->total_minor);
        $this->assertSame(12000, (int) $booking->fare_minor);
    }

    // ── Phase 3: realtime — every transition emits a status event ────

    public function test_every_transition_emits_a_status_changed_event(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);

        // select → pending_acceptance
        $id = $this->svc->select(7, $this->selectPayload(1))['id'];
        $this->assertSame(1, $this->outboxCount($id), 'select must emit');

        // accept → confirmed
        $this->svc->accept(1, $id);
        $this->assertSame(2, $this->outboxCount($id), 'accept must emit');

        // assignDriver → assigned (this is the event that unlocks live tracking)
        $this->svc->assignDriver(1, $id, 33);
        $assigned = EventOutbox::query()->get()->first(fn ($e) => ($e->payload['booking_id'] ?? null) === $id
            && ($e->payload['status'] ?? null) === BookingStatus::ASSIGNED);
        $this->assertNotNull($assigned, 'assignDriver must emit an assigned event');
        $this->assertContains(Channel::user(7), $assigned->channels, 'event must target the rider channel');
        $this->assertSame(3, $this->outboxCount($id));

        // cancel is blocked once a driver is en route, but assigned is still
        // cancellable in this model — cancel → cancelled emits too.
        $this->svc->cancel(7, $id);
        $this->assertSame(4, $this->outboxCount($id), 'cancel must emit');
    }

    public function test_sla_expiry_emits_no_driver_event(): void
    {
        $this->office(1, 'A');
        $this->tariff(1, 12000);
        $this->fund(7, 50000);
        $id = $this->svc->select(7, $this->selectPayload(1, [
            'scheduled_at' => Carbon::now()->addMinutes(10)->toDateTimeString(),
        ]))['id'];

        // Force the SLA into the past so the sweep picks it up.
        \App\Models\FixedTripMeta::query()->where('booking_id', $id)
            ->update(['sla_assign_by' => Carbon::now()->subMinute()]);

        $before = $this->outboxCount($id);
        $this->assertSame(1, $this->svc->expireOverdueAssignments());

        $expired = EventOutbox::query()->get()
            ->first(fn ($e) => ($e->payload['booking_id'] ?? null) === $id
                && ($e->payload['status'] ?? null) === BookingStatus::NO_DRIVER_EXPIRED);
        $this->assertNotNull($expired, 'SLA sweep must emit a no_driver_expired event');
        $this->assertGreaterThan($before, $this->outboxCount($id));
    }

    private function outboxCount(int $bookingId): int
    {
        return EventOutbox::query()->get()
            ->filter(fn ($e) => ($e->payload['booking_id'] ?? null) === $bookingId)
            ->count();
    }
}
