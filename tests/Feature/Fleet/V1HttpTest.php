<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Account\CardGateway;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Models\DeviceToken;
use App\Models\DispatchJob;
use App\Models\Driver;
use App\Models\DriverPresence;
use App\Models\FavoriteOffice;
use App\Models\LedgerPayment;
use App\Models\RideBooking;
use App\Models\RideRating;
use App\Models\ServiceTariff;
use App\Models\User;

/**
 * HTTP contract for the rider + driver money/notification/rating surface.
 *
 * This file used to target an `api/v1/...` prefix that no longer exists. Every
 * case below has been repointed at the LIVE route and re-asserted against what
 * the controllers actually return today. The drift was not just in the URI —
 * response envelopes, status codes and error codes all changed:
 *
 *   old (dead)                       live                       notes
 *   ------------------------------------------------------------------------
 *   POST api/v1/wallet/topups        POST user/wallet/topup     body key is
 *                                                               `amount`, not
 *                                                               `amount_minor`;
 *                                                               returns 200
 *   GET  api/v1/wallet/balance       GET  user/wallet           decimal string
 *                                                               `balance`, no
 *                                                               `balance_minor`
 *   POST api/v1/me/favorites/offices POST user/me/favorites/{id} 204 No Content
 *   POST api/v1/payouts              POST driver/wallet/payouts  error code is
 *                                                               `insufficient_balance`
 *   POST api/v1/quotes               POST user/bookings          standalone fare
 *   POST api/v1/bookings/{id}/hold   POST user/bookings          quoting and
 *                                                               escrow holds were
 *                                                               folded INTO
 *                                                               booking creation
 *   POST api/v1/.../rating           POST user/trips/{id}/rating rider side returns
 *                                                               `{ok:true}`, driver
 *                                                               side returns the row
 *   api/v1/chat/conversations        user/trips/{id}/messages    chat is keyed on
 *                                                               the booking now,
 *                                                               not a conversation
 *
 * Everything hangs off `Reply` (App\Http\Services\User\Support\Reply), so the
 * envelope is always `{status, statusCode, message, data, error, meta, locale}`
 * and failures carry `error.code`.
 *
 * Three cases have NO live equivalent at all and are skipped rather than
 * deleted or faked — see the "driver dues" section.
 */
class V1HttpTest extends FleetTestCase
{
    /**
     * `currencies` lives on the GLOBAL connection (App\Models\Currency pins
     * `$connection = 'global'`), so it belongs here and not in the tenant list.
     * Every money-facing endpoint runs its amount through
     * MoneyPresenter::currency(), which reads this table — without it the whole
     * wallet/payout surface 500s.
     */
    protected array $globalMigrations = [
        '2026_06_19_000002_create_currencies_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_07_17_000002_add_online_time_to_driver_presence.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_07_19_000001_allow_reoffer_on_dispatch_offers.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000009_create_app_notifications_table.php',
        '2026_06_25_000010_create_app_device_tokens_table.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
        '2026_06_25_000016_create_payout_requests_table.php',
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2024_10_29_211028_create_offices_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000002_add_service_to_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_16_000002_add_stops_to_ride_bookings.php',
        '2026_07_17_000003_add_arrived_at_to_ride_bookings.php',
        // supplies ride_bookings.driver_id (+ other later-added rider columns);
        // the driver-side rating scope reads it.
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_11_000005_create_booking_chat_messages_table.php',
    ];

    // ── helpers ─────────────────────────────────────────────────────────────

    private function asUser(int $id = 7): self
    {
        $this->app['auth']->forgetGuards();
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function asDriver(int $id = 9): self
    {
        $this->app['auth']->forgetGuards();
        $driver = new Driver();
        $driver->id = $id;

        return $this->actingAs($driver, 'driver');
    }

    private function seedTariff(int $office = 3): void
    {
        ServiceTariff::query()->create([
            'office_id' => $office, 'service_class' => 'standard', 'currency_code' => 'USD', 'pricing_style' => 'meter',
            'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30, 'minimum_minor' => 1000,
        ]);
    }

    /** The canonical booking payload: 3 km / 10 min → 500 + 600 + 300 = 1400. */
    private function bookingPayload(int $office = 3, string $paymentMethod = 'wallet'): array
    {
        return [
            'office_id' => $office,
            'service' => 'ride',
            'service_class' => 'standard',
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2,
            'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3,
            'distance_m' => 3000, 'duration_s' => 600,
            'payment_method' => $paymentMethod,
        ];
    }

    /**
     * MoneyPresenter::decimal casts to FLOAT before serialising, so `balance`
     * arrives as a JSON number (0, 36.0, …), not the "0.00" string the dead
     * surface returned. Compared loosely so 0 vs 0.0 does not matter.
     */
    private function assertBalance(float $expected, int $userId = 7): void
    {
        $res = $this->asUser($userId)->getJson('user/wallet?currency_code=USD')->assertStatus(200);
        $this->assertEqualsWithDelta($expected, (float) $res->json('data.balance'), 0.001);
    }

    private function seedOffice(string $name): int
    {
        // `email` is NOT NULL + unique and `password` is NOT NULL on the legacy
        // offices table, so both must be supplied even though nothing here uses them.
        $office = \App\Models\Office::query()->create([
            'officeName' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '@example.test',
            'password' => 'x',
            'contactNumber' => '100',
            'country' => 'QA',
            'city' => 'Doha',
        ]);

        return (int) $office->id;
    }

    private function fundWallet(int $userId, int $minor): void
    {
        (new \App\Http\Core\Classes\Ledger\FleetWalletService(new \App\Http\Core\Classes\Ledger\LedgerService()))
            ->topUp($userId, $minor, 'USD', 'fund-' . $userId . '-' . $minor, 'test', 1);
    }

    /** A booking row created directly, bypassing pricing/dispatch. */
    private function seedRideBooking(int $id, int $userId, ?int $driverId = null, string $status = 'assigned'): void
    {
        $booking = new RideBooking();
        $booking->id = $id;
        $booking->forceFill([
            'user_id' => $userId,
            'office_id' => 3,
            'service' => 'ride',
            'service_class' => 'standard',
            'pricing_style' => 'meter',
            'status' => $status,
            'driver_id' => $driverId,
            'pickup_lat' => 25.1, 'pickup_lng' => 51.2,
            'dropoff_lat' => 25.2, 'dropoff_lng' => 51.3,
            'currency_code' => 'USD',
        ]);
        $booking->save();
    }

    private function assign(int $bookingId, int $driverId = 9): void
    {
        DispatchJob::query()->create([
            'booking_id' => $bookingId, 'office_id' => 3, 'service_class' => 'standard',
            'lat' => 25.1, 'lng' => 51.2, 'status' => DispatchStatus::ASSIGNED,
            'assigned_driver_id' => $driverId, 'wave' => 1,
        ]);
    }

    /**
     * A stand-in card gateway.
     *
     * With no `services.stripe.secret` configured the container binds
     * NullCardGateway, whose paymentIntent() refuses with a 503
     * `payments_unavailable` (see test_wallet_topup_without_gateway_is_503) — so
     * the real top-up success path is unreachable in a test environment. Binding
     * a double lets us still pin the part this test owns: that a successful
     * gateway call is recorded as a PENDING ledger intent (the wallet is only
     * credited later, by the webhook).
     */
    private function fakeCardGateway(): void
    {
        $this->app->bind(CardGateway::class, fn () => new class implements CardGateway
        {
            public function setupIntent(int $userId): array
            {
                return ['clientSecret' => 'seti_secret'];
            }

            public function describe(string $token): ?array
            {
                return null;
            }

            public function paymentIntent(int $userId, int $amountMinor, string $currency, ?int $paymentMethodId, string $idempotencyKey, array $metadata = [], bool $manualCapture = false): array
            {
                return [
                    'id' => 'pi_test_' . $idempotencyKey,
                    'status' => $manualCapture ? 'requires_capture' : 'requires_payment_method',
                    'clientSecret' => 'pi_secret',
                    'requiresAction' => false,
                ];
            }

            public function capturePaymentIntent(string $paymentIntentId, int $amountToCaptureMinor): string
            {
                return 'succeeded';
            }

            public function cancelPaymentIntent(string $paymentIntentId): void
            {
            }

            public function paymentIntentStatus(string $paymentIntentId): ?string
            {
                return 'succeeded';
            }
        });
    }

    // ── wallet ──────────────────────────────────────────────────────────────

    public function test_unauthenticated_wallet_topup_is_401(): void
    {
        $this->postJson('user/wallet/topup', ['amount' => 10000])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_unauthenticated_wallet_balance_is_401(): void
    {
        $this->getJson('user/wallet')->assertStatus(401);
    }

    /**
     * The old suite asserted an `Idempotency-Key` HEADER was mandatory. It no
     * longer is — WalletService derives `topup:{user}:{amount}` when the header
     * is absent. What IS mandatory is the `amount` body field (and note the
     * rename: the dead surface took `amount_minor`).
     */
    public function test_wallet_topup_requires_amount(): void
    {
        $this->fakeCardGateway();

        $this->asUser()
            ->postJson('user/wallet/topup', ['currency_code' => 'USD'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_wallet_topup_rejects_non_positive_amount(): void
    {
        $this->fakeCardGateway();

        $this->asUser()
            ->postJson('user/wallet/topup', ['amount' => 0])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /**
     * With no gateway configured the top-up is UNAVAILABLE, not broken: a 503
     * the app can show as "payments temporarily unavailable". This used to be a
     * 500 `server_error` — NullCardGateway threw a bare RuntimeException that
     * Reply::fromException could not classify, so an unconfigured gateway looked
     * like a backend crash on the rider's top-up screen. No double is bound
     * here on purpose: this is the real NullCardGateway path.
     */
    public function test_wallet_topup_without_gateway_is_503(): void
    {
        $this->asUser()
            ->postJson('user/wallet/topup', ['amount' => 10000, 'currency_code' => 'USD'])
            ->assertStatus(503)
            ->assertJsonPath('error.code', 'payments_unavailable');
    }

    /**
     * A top-up returns 200 (not 201) and does NOT move money: it records a
     * PENDING LedgerPayment plus the gateway intent the app must confirm. The
     * wallet is credited later by PaymentService::handleGatewayEvent.
     */
    public function test_wallet_topup_creates_pending_intent(): void
    {
        $this->fakeCardGateway();

        $this->asUser()
            ->postJson('user/wallet/topup', ['amount' => 10000], ['Idempotency-Key' => 'k-http-1'])
            ->assertStatus(200)
            ->assertJsonPath('data.paymentIntentId', 'pi_test_k-http-1')
            ->assertJsonPath('data.status', 'requires_payment_method');

        $intent = LedgerPayment::query()->where('idempotency_key', 'k-http-1')->first();
        $this->assertNotNull($intent);
        $this->assertSame('pending', $intent->status);
        $this->assertSame(10000, (int) $intent->amount_minor);

        // still pending → balance untouched
        $this->assertBalance(0);
    }

    /**
     * Balance is presented as a decimal STRING in the currency's own precision;
     * the old `balance_minor` / `owner_type` keys are gone.
     */
    public function test_wallet_balance_starts_zero(): void
    {
        $this->asUser()
            ->getJson('user/wallet?currency_code=USD')
            ->assertStatus(200)
            ->assertJsonPath('data.currency', 'USD')
            ->assertJsonPath('data.decimals', 2);

        $this->assertBalance(0);
    }

    public function test_wallet_balance_reflects_credited_funds(): void
    {
        $this->fundWallet(7, 5000);

        $this->assertBalance(50);
    }

    // ── devices ─────────────────────────────────────────────────────────────

    /**
     * The device row is scoped to the caller server-side; the response no longer
     * echoes `owner_type` / `owner_id`, so ownership is asserted at the row.
     */
    public function test_register_device_as_user(): void
    {
        $this->asUser()
            ->postJson('user/devices', ['token' => 'fcm-tok-1', 'platform' => 'android'])
            ->assertStatus(201)
            ->assertJsonPath('data.token', 'fcm-tok-1')
            ->assertJsonPath('data.platform', 'android');

        $device = DeviceToken::query()->where('token', 'fcm-tok-1')->first();
        $this->assertNotNull($device);
        $this->assertSame('user', $device->owner_type);
        $this->assertSame(7, (int) $device->owner_id);
    }

    public function test_register_device_requires_token(): void
    {
        $this->asUser()
            ->postJson('user/devices', ['platform' => 'android'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_register_device_rejects_unknown_platform(): void
    {
        $this->asUser()
            ->postJson('user/devices', ['token' => 'tok', 'platform' => 'symbian'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_unauthenticated_device_registration_is_401(): void
    {
        $this->postJson('user/devices', ['token' => 'tok'])->assertStatus(401);
    }

    // ── notifications ───────────────────────────────────────────────────────

    /**
     * `data` is an OBJECT now (items + unreadCount + nextCursor), not a bare
     * array with a `meta.has_more` sibling.
     */
    public function test_notifications_list_empty(): void
    {
        $this->asUser()
            ->getJson('user/notifications')
            ->assertStatus(200)
            ->assertJsonPath('data.items', [])
            ->assertJsonPath('data.unreadCount', 0)
            ->assertJsonPath('data.nextCursor', null);
    }

    public function test_unauthenticated_notifications_is_401(): void
    {
        $this->getJson('user/notifications')->assertStatus(401);
    }

    /** Reading someone else's notification is a 404, not a 403. */
    public function test_reading_unknown_notification_is_404(): void
    {
        $this->asUser()
            ->postJson('user/notifications/4242/read')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    // ── favorite offices ────────────────────────────────────────────────────

    /**
     * The path lost its `/offices` segment and both writes now answer
     * 204 No Content; the index returns full office CARDS, so it only surfaces
     * favorites whose office row still exists.
     */
    public function test_favorite_office_flow(): void
    {
        $officeId = $this->seedOffice('Test Office');

        $this->asUser()->postJson("user/me/favorites/{$officeId}")->assertStatus(204);
        $this->assertSame(1, FavoriteOffice::query()->where('user_id', 7)->where('office_id', $officeId)->count());

        $this->asUser()->getJson('user/me/favorites')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $officeId);

        $this->asUser()->deleteJson("user/me/favorites/{$officeId}")->assertStatus(204);
        $this->assertSame(0, FavoriteOffice::query()->where('user_id', 7)->count());

        $this->asUser()->getJson('user/me/favorites')->assertStatus(200)->assertJsonPath('data', []);
    }

    /** Favorites are per-rider: one user's list never leaks into another's. */
    public function test_favorites_are_scoped_to_the_caller(): void
    {
        $officeId = $this->seedOffice('Scoped Office');

        $this->asUser(7)->postJson("user/me/favorites/{$officeId}")->assertStatus(204);

        $this->asUser(8)->getJson('user/me/favorites')->assertStatus(200)->assertJsonPath('data', []);
    }

    public function test_unauthenticated_favorites_is_401(): void
    {
        $this->getJson('user/me/favorites')->assertStatus(401);
    }

    // ── driver presence ─────────────────────────────────────────────────────

    /**
     * The heartbeat response was trimmed to `{status}` — the office is taken
     * from the driver's own record, and `driver_id` is no longer echoed, so the
     * row itself is what proves the heartbeat landed against driver 9.
     */
    public function test_driver_presence_heartbeat(): void
    {
        $this->asDriver()
            ->postJson('driver/presence', ['status' => 'online', 'lat' => 25.1, 'lng' => 51.2])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'online');

        $presence = DriverPresence::query()->find(9);
        $this->assertNotNull($presence);
        $this->assertSame('online', $presence->status);
    }

    public function test_driver_presence_rejects_unknown_status(): void
    {
        $this->asDriver()
            ->postJson('driver/presence', ['status' => 'sleeping'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_unauthenticated_presence_is_401(): void
    {
        $this->postJson('driver/presence', ['status' => 'online'])->assertStatus(401);
    }

    // ── dispatch offers ─────────────────────────────────────────────────────

    /**
     * Accepting an offer that was never made still conflicts, but the code is
     * `offer_unavailable` now — `already_assigned` was renamed because the same
     * 409 covers expired/withdrawn/taken offers, not just taken ones.
     */
    public function test_driver_accept_without_offer_is_409(): void
    {
        $this->asDriver()
            ->postJson('driver/offers/5001/accept')
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'offer_unavailable');
    }

    public function test_unauthenticated_offer_accept_is_401(): void
    {
        $this->postJson('driver/offers/5001/accept')->assertStatus(401);
    }

    // ── driver payouts ──────────────────────────────────────────────────────

    /** PayoutService::request → `insufficient_balance` (was `insufficient_funds`). */
    public function test_driver_payout_request_rejected_when_no_balance(): void
    {
        $this->asDriver()
            ->postJson('driver/wallet/payouts', ['amount_minor' => 5000, 'currency_code' => 'USD'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'insufficient_balance');
    }

    public function test_driver_payout_requires_positive_amount(): void
    {
        $this->asDriver()
            ->postJson('driver/wallet/payouts', ['amount_minor' => 0, 'currency_code' => 'USD'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_unauthenticated_payout_is_401(): void
    {
        $this->postJson('driver/wallet/payouts', ['amount_minor' => 5000])->assertStatus(401);
    }

    // ── driver dues ─────────────────────────────────────────────────────────
    //
    // `GET driver/dues` / `POST driver/dues/settle` surface the outstanding
    // cash-trip commission the driver OWES the fleet and let them settle it from
    // their wallet — the money-out mirror of the panel's staff-side settle.

    /** Cash-trip commission booked as driver dues (mirror of DriverDuesTest). */
    private function seedDriverDues(int $driverId, int $totalMinor = 10000): void
    {
        (new \App\Http\Core\Classes\Ledger\FleetWalletService(new \App\Http\Core\Classes\Ledger\LedgerService()))
            ->cashCommission([
                'booking_id' => 7100 + $driverId,
                'office_id' => 3,
                'driver_id' => $driverId,
                'currency_code' => 'USD',
                'total_minor' => $totalMinor,
                'fleet_rate' => 18.0,
                'office_rate' => 0.0,
            ]);
    }

    public function test_driver_dues_show_starts_zero(): void
    {
        $this->asDriver(9)
            ->getJson('driver/dues?currency_code=USD')
            ->assertStatus(200)
            ->assertJsonPath('data.dues_minor', 0);
    }

    public function test_driver_dues_settle_from_wallet_clears_debt(): void
    {
        $this->seedDriverDues(9);          // 1,800 dues (18% of 10,000)

        // The driver wallet is normally funded by a released ride; credit it
        // directly through the ledger here so there is balance to settle from.
        (new \App\Http\Core\Classes\Ledger\FleetWalletService(new \App\Http\Core\Classes\Ledger\LedgerService()))
            ->adjustment([
                ['owner_type' => 'fleet', 'owner_id' => 0, 'account_type' => 'revenue', 'direction' => 'debit', 'amount_minor' => 5000],
                ['owner_type' => 'driver', 'owner_id' => 9, 'account_type' => 'wallet', 'direction' => 'credit', 'amount_minor' => 5000],
            ], 'USD', 'seed-driver-wallet-9');

        $this->asDriver(9)
            ->postJson('driver/dues/settle', ['currency_code' => 'USD'])
            ->assertStatus(200)
            ->assertJsonPath('data.settled_minor', 1800)
            ->assertJsonPath('data.remaining_dues_minor', 0);
    }

    public function test_driver_dues_settle_with_no_dues_is_422(): void
    {
        $this->asDriver(9)
            ->postJson('driver/dues/settle', ['currency_code' => 'USD'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'no_dues');
    }

    // ── fare quoting ────────────────────────────────────────────────────────
    //
    // The standalone `POST api/v1/quotes` endpoint is gone. Quoting was folded
    // into booking creation: RideBookingService::create resolves the tariff and
    // runs the SAME PricingService the old quote endpoint used, so the fare maths
    // (meter: base + per-km + per-minute, floored at `minimum_minor`) is still
    // pinned here — just through the endpoint that actually ships.

    public function test_booking_quotes_fare_from_tariff(): void
    {
        $this->seedTariff();
        $this->fundWallet(7, 5000);

        $this->asUser()
            ->postJson('user/bookings', $this->bookingPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.fare_minor', 1400)   // 500 + 200*3km + 30*10min
            ->assertJsonPath('data.currency_code', 'USD')
            ->assertJsonPath('data.pricing_style', 'meter');
    }

    /** `minimum_minor` floors a short trip. */
    public function test_booking_fare_is_floored_at_the_tariff_minimum(): void
    {
        $this->seedTariff();
        $this->fundWallet(7, 5000);

        $this->asUser()
            ->postJson('user/bookings', array_merge($this->bookingPayload(), ['distance_m' => 100, 'duration_s' => 60]))
            ->assertStatus(201)
            ->assertJsonPath('data.fare_minor', 1000);
    }

    public function test_booking_missing_tariff_is_404(): void
    {
        $this->asUser()
            ->postJson('user/bookings', $this->bookingPayload(999))
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'tariff_not_found');
    }

    public function test_unauthenticated_booking_create_is_401(): void
    {
        $this->postJson('user/bookings', $this->bookingPayload())->assertStatus(401);
    }

    // ── escrow hold ─────────────────────────────────────────────────────────
    //
    // `POST api/v1/bookings/{id}/hold` is gone and BookingHoldService is now
    // unrouted dead code. The hold itself survives: creating a wallet-paid
    // booking moves the total into ride escrow inside the same transaction.

    public function test_booking_hold_moves_wallet_to_escrow(): void
    {
        $this->seedTariff();
        $this->fundWallet(7, 5000);

        $this->asUser()
            ->postJson('user/bookings', $this->bookingPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.held_minor', 1400)
            ->assertJsonPath('data.payment_method', 'wallet');

        // 5000 funded − 1400 escrowed
        $this->assertBalance(36);
    }

    public function test_booking_hold_insufficient_balance_is_422(): void
    {
        $this->seedTariff();

        $this->asUser()
            ->postJson('user/bookings', $this->bookingPayload())
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'insufficient_funds');

        // the failed hold rolled the whole booking back
        $this->assertSame(0, RideBooking::query()->count());
    }

    /** Cash rides skip the wallet entirely — no balance required, nothing held. */
    public function test_cash_booking_holds_nothing(): void
    {
        $this->seedTariff();

        $this->asUser()
            ->postJson('user/bookings', $this->bookingPayload(3, 'cash'))
            ->assertStatus(201)
            ->assertJsonPath('data.held_minor', 0);
    }

    // ── ratings ─────────────────────────────────────────────────────────────

    /**
     * The rider rating endpoint returns a bare `{ok:true}` — not the rating row —
     * and writes TWO ratings: one for the assigned driver (from the dispatch
     * job) and one for the office. Hence the row-level assertions.
     */
    public function test_rider_rates_assigned_driver(): void
    {
        $this->seedRideBooking(8801, 7, 9, 'completed');
        $this->assign(8801, 9);

        $this->asUser(7)
            ->postJson('user/trips/8801/rating', ['stars' => 5, 'comment' => 'great'])
            ->assertStatus(200)
            ->assertJsonPath('data.ok', true);

        $driverRating = RideRating::query()->where('booking_id', 8801)->where('ratee_type', 'driver')->first();
        $this->assertNotNull($driverRating);
        $this->assertSame(9, (int) $driverRating->ratee_id);
        $this->assertSame(5, (int) $driverRating->stars);

        // the office is always rated too
        $this->assertSame(1, RideRating::query()->where('booking_id', 8801)->where('ratee_type', 'office')->count());

        $this->assertNotNull(RideBooking::query()->find(8801)->rated_at);
    }

    /**
     * A ride that has not completed cannot be rated. A booking still in
     * `matching` (no driver, no trip) is refused with 409 `ride_not_rateable`,
     * and nothing is written — otherwise a rider could record a driver/office
     * rating for a trip that never ran.
     */
    public function test_rating_a_ride_that_has_not_completed_is_409(): void
    {
        $this->seedRideBooking(8802, 7, null, 'matching');

        $this->asUser(7)
            ->postJson('user/trips/8802/rating', ['stars' => 5])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'ride_not_rateable');

        $this->assertSame(0, RideRating::query()->where('booking_id', 8802)->count());
    }

    /** …and an on-trip (not yet completed) ride is likewise refused. */
    public function test_rating_an_in_progress_ride_is_409(): void
    {
        $this->seedRideBooking(8803, 7, 9, 'on_trip');

        $this->asUser(7)
            ->postJson('user/trips/8803/rating', ['stars' => 5])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'ride_not_rateable');
    }

    /** A completed ride rates normally (both driver and office rows land). */
    public function test_rating_a_completed_ride_succeeds(): void
    {
        $this->seedRideBooking(8804, 7, 9, 'completed');
        $this->assign(8804, 9);

        $this->asUser(7)
            ->postJson('user/trips/8804/rating', ['stars' => 5])
            ->assertStatus(200)
            ->assertJsonPath('data.ok', true);

        $this->assertSame(1, RideRating::query()->where('booking_id', 8804)->where('ratee_type', 'office')->count());
    }

    public function test_rider_rating_rejects_out_of_range_stars(): void
    {
        $this->seedRideBooking(8805, 7, 9);

        $this->asUser(7)
            ->postJson('user/trips/8805/rating', ['stars' => 6])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /** A stranger gets 404 — the endpoint will not confirm the trip exists. */
    public function test_rate_foreign_booking_is_404(): void
    {
        $this->seedRideBooking(8803, 7, 9);
        $this->assign(8803, 9);

        $this->asUser(8)
            ->postJson('user/trips/8803/rating', ['stars' => 1])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    /**
     * The driver side DOES return the rating row (201), and the ratee type is
     * the literal `rider` — NOT `user`, which is what the dead surface used and
     * what RatingService still uses for its own channel mapping.
     *
     * Driver ownership is keyed on `ride_bookings.driver_id`, not the dispatch
     * job, so the booking must carry the driver id.
     */
    public function test_driver_rates_rider(): void
    {
        $this->seedRideBooking(8804, 7, 9);
        $this->assign(8804, 9);

        $this->asDriver(9)
            ->postJson('driver/trips/8804/rating', ['stars' => 4])
            ->assertStatus(201)
            ->assertJsonPath('data.ratee_type', 'rider')
            ->assertJsonPath('data.ratee_id', 7)
            ->assertJsonPath('data.rater_type', 'driver')
            ->assertJsonPath('data.stars', 4);
    }

    public function test_driver_cannot_rate_a_trip_they_did_not_drive(): void
    {
        $this->seedRideBooking(8806, 7, 9);
        $this->assign(8806, 9);

        $this->asDriver(99)
            ->postJson('driver/trips/8806/rating', ['stars' => 4])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_unauthenticated_rating_is_401(): void
    {
        $this->postJson('user/trips/8801/rating', ['stars' => 5])->assertStatus(401);
    }

    // ── trip chat ───────────────────────────────────────────────────────────
    //
    // `api/v1/chat/conversations` (an office-scoped conversation resource) is
    // gone. The rider now messages on the BOOKING: there is no conversation to
    // create, and the chat window is governed by the dispatch assignment.

    public function test_chat_send_and_list(): void
    {
        $this->seedRideBooking(9001, 7, 9);
        $this->assign(9001, 9);

        $this->asUser(7)
            ->postJson('user/trips/9001/messages', ['body' => 'Hello office'])
            ->assertStatus(201)
            ->assertJsonPath('data.from_type', 'rider')
            ->assertJsonPath('data.body', 'Hello office');

        $this->asUser(7)
            ->getJson('user/trips/9001/messages')
            ->assertStatus(200)
            ->assertJsonPath('data.items.0.body', 'Hello office')
            ->assertJsonPath('data.nextCursor', null);
    }

    /** Before a driver is assigned there is nobody to talk to → 403. */
    public function test_chat_before_assignment_is_403(): void
    {
        $this->seedRideBooking(9002, 7, null, 'matching');

        $this->asUser(7)
            ->postJson('user/trips/9002/messages', ['body' => 'anyone there'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'chat_unavailable');
    }

    public function test_chat_send_to_foreign_trip_is_404(): void
    {
        $this->seedRideBooking(9003, 7, 9);
        $this->assign(9003, 9);

        $this->asUser(8)
            ->postJson('user/trips/9003/messages', ['body' => 'intrude'])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_chat_read_of_foreign_trip_is_404(): void
    {
        $this->seedRideBooking(9004, 7, 9);
        $this->assign(9004, 9);

        $this->asUser(8)->getJson('user/trips/9004/messages')->assertStatus(404);
    }

    public function test_chat_requires_a_body(): void
    {
        $this->seedRideBooking(9005, 7, 9);
        $this->assign(9005, 9);

        $this->asUser(7)
            ->postJson('user/trips/9005/messages', [])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }
}
