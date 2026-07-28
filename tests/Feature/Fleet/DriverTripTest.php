<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Models\DispatchJob;
use App\Models\DispatchOffer;
use App\Models\Driver;
use App\Models\RideBooking;
use App\Models\ServiceTariff;

class DriverTripTest extends FleetTestCase
{
    // Currency resolution reads the default country/currency off the global
    // connection.
    protected array $globalMigrations = [
        '2024_11_12_070712_create_countries_table.php',
        '2026_06_19_000002_create_currencies_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        // Columns the live trip lifecycle writes.
        '2026_07_15_000001_add_rider_api_missing_columns.php', // driver_id, vehicle_id, …
        '2026_07_17_000003_add_arrived_at_to_ride_bookings.php',
        '2026_07_19_000001_allow_reoffer_on_dispatch_offers.php',
        // Ride details resolve the rider for the driver's screen.
        '2024_10_23_085910_create_users_table.php',
        // The offer card shows the rider's mid-route stops.
        '2026_07_16_000002_add_stops_to_ride_bookings.php',
    ];

    private function asDriver(int $id = 9): self
    {
        $this->app['auth']->forgetGuards();
        $d = new Driver();
        $d->id = $id;

        return $this->actingAs($d, 'driver');
    }

    private function wallet(): FleetWalletService
    {
        return new FleetWalletService(new LedgerService());
    }

    private function seedAssignedBooking(int $bookingId, int $driverId = 9, int $total = 10000, string $payment = 'wallet'): void
    {
        $b = new RideBooking();
        $b->id = $bookingId;
        $b->forceFill([
            'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'matching',
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2, 'pickup_title' => 'Home',
            'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3, 'dropoff_title' => 'Airport',
            'currency_code' => 'USD', 'fare_minor' => $total, 'total_minor' => $total,
            'held_minor' => $payment === 'wallet' ? $total : 0, 'payment_method' => $payment,
            // Assignment stamps the winner onto the booking (DispatchService::accept
            // does this in production); `driver/home` looks the active trip up by it.
            'driver_id' => $driverId, 'assigned_at' => now(),
        ]);
        $b->save();

        if ($payment === 'wallet') {
            $w = $this->wallet();
            $w->topUp(7, $total, 'USD', 'fund:' . $bookingId, 'test', 1);
            $w->holdRide($bookingId, 7, $total, 'USD', 'hold:' . $bookingId);
        }

        DispatchJob::query()->create([
            'booking_id' => $bookingId, 'office_id' => 3, 'service_class' => 'standard',
            'lat' => 25.1, 'lng' => 51.2, 'status' => DispatchStatus::ASSIGNED,
            'assigned_driver_id' => $driverId, 'wave' => 1,
        ]);
    }

    /**
     * A claimed SCHEDULED trip and an office-assigned FIXED trip (status
     * 'assigned') must be able to start their pickup drive — previously
     * navigate-pickup only accepted matching/arriving, so these 409'd and the
     * rider never saw the driver move.
     */
    public function test_navigate_from_scheduled_or_assigned_starts_pickup(): void
    {
        $this->seedAssignedBooking(960);
        RideBooking::query()->whereKey(960)->update(['status' => 'scheduled']);
        $this->asDriver()->postJson('driver/trips/960/navigate-pickup')
            ->assertStatus(200)->assertJsonPath('data.status', 'arriving');

        $this->seedAssignedBooking(961);
        RideBooking::query()->whereKey(961)->update(['status' => 'assigned']);
        $this->asDriver()->postJson('driver/trips/961/navigate-pickup')
            ->assertStatus(200)->assertJsonPath('data.status', 'arriving');
    }

    /**
     * REGRESSION: claiming a scheduled offer must create the DISPATCH-JOB
     * assignment, not only stamp `bookings.driver_id`. Every trip-stage endpoint
     * gates on `DispatchJobRepository::assignmentForDriver` (a DispatchJob row) —
     * a scheduled booking has no job, so a successful claim left the driver with
     * 403 "ride_not_assigned" on navigate/arrived/start/end. The other tests here
     * seed the job by hand, so they never caught it; this one drives the REAL
     * `claim` endpoint end-to-end.
     */
    public function test_claiming_a_scheduled_offer_lets_the_driver_drive_it(): void
    {
        $d = new Driver();
        $d->id = 9;
        $d->officeId = 3;
        $this->actingAs($d, 'driver');

        // An unclaimed scheduled booking in the driver's office — and NO dispatch job.
        $b = new RideBooking();
        $b->id = 970;
        $b->forceFill([
            'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'scheduled',
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2, 'pickup_title' => 'Home',
            'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3, 'dropoff_title' => 'Airport',
            'currency_code' => 'USD', 'fare_minor' => 10000, 'total_minor' => 10000,
            'held_minor' => 0, 'payment_method' => 'cash', 'driver_id' => null,
        ]);
        $b->save();

        $this->assertNull(DispatchJob::query()->where('booking_id', 970)->first(), 'no dispatch job before claim');

        $this->postJson('driver/scheduled/offers/970/claim')->assertStatus(200);

        // The claim now registers the assignment the stage endpoints look for.
        $this->assertSame(9, (int) DispatchJob::query()->where('booking_id', 970)->value('assigned_driver_id'));

        // …so the claimed scheduled ride can actually be driven (was 403 before).
        $this->postJson('driver/trips/970/navigate-pickup')
            ->assertStatus(200)->assertJsonPath('data.status', 'arriving');
        $this->postJson('driver/trips/970/arrived')
            ->assertStatus(200)->assertJsonPath('data.status', 'arrived');
    }

    /**
     * REGRESSION: ending a ride whose total settled to zero (e.g. a scheduled
     * meter trip ended before any distance accrued) must COMPLETE, not 500. The
     * three-way ledger release asserts a positive amount, so a 0-fare settle
     * threw "amount must be positive" and 500'd the driver's "end". Nothing to
     * split → skip settlement and complete cleanly.
     */
    public function test_ending_a_zero_total_ride_completes_without_500(): void
    {
        $this->seedAssignedBooking(980, total: 0, payment: 'cash');
        RideBooking::query()->whereKey(980)->update(['status' => 'on_trip']);

        $this->asDriver()->postJson('driver/trips/980/end')
            ->assertStatus(200)->assertJsonPath('data.status', 'completed');

        $this->assertSame('completed', RideBooking::query()->find(980)->status);
    }

    public function test_full_lifecycle_digital_settles_and_completes(): void
    {
        $this->seedAssignedBooking(900);

        $this->asDriver()->postJson('driver/trips/900/navigate-pickup')->assertStatus(200)->assertJsonPath('data.status', 'arriving');
        $this->asDriver()->postJson('driver/trips/900/arrived')->assertStatus(200)->assertJsonPath('data.status', 'arrived');
        $this->asDriver()->postJson('driver/trips/900/start')->assertStatus(200)->assertJsonPath('data.status', 'on_trip');
        $this->asDriver()->postJson('driver/trips/900/end', ['distance_m' => 4800, 'duration_s' => 720])
            ->assertStatus(200)->assertJsonPath('data.status', 'completed');

        $this->assertSame(8200, $this->wallet()->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame('completed', RideBooking::query()->find(900)->status);
    }

    public function test_cancel_before_start_refunds_escrow(): void
    {
        $this->seedAssignedBooking(901);

        $this->asDriver()->postJson('driver/trips/901/cancel', ['reason' => 'vehicle_issue'])
            ->assertStatus(200)->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(10000, $this->wallet()->walletBalanceMinor('user', 7, 'USD'));
        $this->assertSame(0, $this->wallet()->escrowBalanceMinor(901, 'USD'));
    }

    public function test_cannot_cancel_after_start(): void
    {
        $this->seedAssignedBooking(902);

        $this->asDriver()->postJson('driver/trips/902/arrived')->assertStatus(200);
        $this->asDriver()->postJson('driver/trips/902/start')->assertStatus(200);

        $this->asDriver()->postJson('driver/trips/902/cancel')->assertStatus(409)->assertJsonPath('error.code', 'not_cancellable');
    }

    public function test_foreign_driver_cannot_drive_trip(): void
    {
        $this->seedAssignedBooking(903, 9);

        $this->asDriver(8)->postJson('driver/trips/903/start')
            ->assertStatus(403)->assertJsonPath('error.code', 'ride_not_assigned');
    }

    public function test_meter_reconciliation_refunds_excess(): void
    {
        ServiceTariff::query()->create([
            'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard', 'currency_code' => 'USD',
            'pricing_style' => 'meter', 'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30, 'minimum_minor' => 1000,
        ]);
        $this->seedAssignedBooking(920);

        foreach (['navigate-pickup', 'arrived', 'start'] as $step) {
            $this->asDriver()->postJson("driver/trips/920/{$step}")->assertStatus(200);
        }

        $final = $this->asDriver()->postJson('driver/trips/920/end', ['distance_m' => 2000, 'duration_s' => 120])
            ->assertStatus(200)->json('data.total_minor');

        $this->assertLessThan(10000, $final);
        $this->assertSame(10000 - $final, $this->wallet()->walletBalanceMinor('user', 7, 'USD'));
        $this->assertSame(0, $this->wallet()->escrowBalanceMinor(920, 'USD'));
        $this->assertSame('completed', RideBooking::query()->find(920)->status);
    }

    public function test_meter_caps_actual_at_expected_quote_on_deviation(): void
    {
        ServiceTariff::query()->create([
            'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard', 'currency_code' => 'USD',
            'pricing_style' => 'meter', 'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30, 'minimum_minor' => 1000,
        ]);
        // Expected quote at booking = 1000. Cash → no escrow to cap it, so the
        // min(actual, expected) rule must do the capping itself.
        $this->seedAssignedBooking(921, 9, 1000, 'cash');

        foreach (['navigate-pickup', 'arrived', 'start'] as $step) {
            $this->asDriver()->postJson("driver/trips/921/{$step}")->assertStatus(200);
        }

        // Driver drove 20km (actual meter ≈ 4500) — a detour far beyond the route.
        $final = $this->asDriver()->postJson('driver/trips/921/end', ['distance_m' => 20000, 'duration_s' => 0])
            ->assertStatus(200)->json('data.total_minor');

        $this->assertSame(1000, $final); // capped at the expected quote, not the inflated actual
    }

    public function test_home_shows_active_trip(): void
    {
        $this->seedAssignedBooking(911);
        $this->asDriver()->postJson('driver/trips/911/navigate-pickup')->assertStatus(200);

        $this->asDriver()->getJson('driver/home')
            ->assertStatus(200)
            ->assertJsonPath('data.active_trip.booking_id', 911)
            ->assertJsonPath('data.active_trip.status', 'arriving');
    }

    public function test_earnings_history_and_home_after_completion(): void
    {
        $this->seedAssignedBooking(910);
        foreach (['navigate-pickup', 'arrived', 'start', 'end'] as $step) {
            $this->asDriver()->postJson("driver/trips/910/{$step}")->assertStatus(200);
        }

        $e = $this->asDriver()->getJson('driver/earnings?period=today')->assertStatus(200);
        $this->assertSame(1, $e->json('data.trips'));
        $this->assertSame(8200, $e->json('data.digital_earnings_minor'));
        $this->assertSame(8200, $e->json('data.wallet_balance_minor'));
        $this->assertSame(8200, $e->json('data.net_expected_payout_minor'));

        $this->asDriver()->getJson('driver/trips/history')
            ->assertStatus(200)
            // `driver/trips/history` returns BookingPresenter rows under `items`
            // — the same shape the driver app parses via Trip.fromJson.
            ->assertJsonPath('data.items.0.id', 910)
            ->assertJsonPath('data.items.0.status', 'completed');

        $this->asDriver()->getJson('driver/trips/910')->assertStatus(200)->assertJsonPath('data.booking.id', 910);

        $this->asDriver()->getJson('driver/home')
            ->assertStatus(200)
            ->assertJsonPath('data.trips_today', 1)
            ->assertJsonPath('data.today_earned_minor', 8200)
            ->assertJsonPath('data.active_trip', null);
    }

    /**
     * A driver being OFFERED a ride must be able to read its details — fare,
     * payment method, route and stops — before deciding. Dispatch only stamps
     * `driver_id` on accept, so an ownership-only check 404s here and leaves the
     * offer card blank.
     */
    public function test_offered_driver_can_read_trip_before_accepting(): void
    {
        $b = new RideBooking();
        $b->id = 950;
        $b->forceFill([
            'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'offered',
            'pickup_lat' => 33.5138, 'pickup_lng' => 36.2765, 'pickup_title' => 'Umayyad Square',
            'dropoff_lat' => 33.49, 'dropoff_lng' => 36.33, 'dropoff_title' => 'Airport',
            'stops' => [['lat' => 33.502, 'lng' => 36.29, 'title' => 'Mazzeh']],
            'currency_code' => 'SYP', 'fare_minor' => 2675, 'total_minor' => 2675,
            'held_minor' => 0, 'payment_method' => 'cash',
            // Not assigned to anyone yet — this is the whole point.
            'driver_id' => null,
        ]);
        $b->save();

        DispatchOffer::query()->create([
            'booking_id' => 950, 'driver_id' => 9, 'office_id' => 3, 'wave' => 1,
            'status' => OfferStatus::OFFERED, 'distance_m' => 800, 'expires_at' => now()->addSeconds(20),
        ]);

        $this->asDriver(9)->getJson('driver/trips/950')
            ->assertStatus(200)
            ->assertJsonPath('data.booking.payment_method', 'cash')
            ->assertJsonPath('data.booking.total_minor', 2675)
            ->assertJsonPath('data.booking.currency_code', 'SYP')
            ->assertJsonPath('data.booking.pricing_style', 'meter')
            ->assertJsonPath('data.booking.stops.0.title', 'Mazzeh');

        // A driver with no offer on this booking still gets nothing.
        $this->asDriver(11)->getJson('driver/trips/950')->assertStatus(404);
    }

    /** An expired offer stops granting read access. */
    public function test_expired_offer_cannot_read_trip(): void
    {
        $b = new RideBooking();
        $b->id = 951;
        $b->forceFill([
            'user_id' => 7, 'office_id' => 3, 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'meter', 'status' => 'offered',
            'pickup_lat' => 33.5, 'pickup_lng' => 36.2, 'dropoff_lat' => 33.4, 'dropoff_lng' => 36.3,
            'currency_code' => 'SYP', 'fare_minor' => 1000, 'total_minor' => 1000,
            'held_minor' => 0, 'payment_method' => 'cash', 'driver_id' => null,
        ]);
        $b->save();

        DispatchOffer::query()->create([
            'booking_id' => 951, 'driver_id' => 9, 'office_id' => 3, 'wave' => 1,
            'status' => OfferStatus::OFFERED, 'distance_m' => 800, 'expires_at' => now()->subSecond(),
        ]);

        $this->asDriver(9)->getJson('driver/trips/951')->assertStatus(404);
    }
}
