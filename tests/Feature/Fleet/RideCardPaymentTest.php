<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Account\CardGateway;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerIntegrityService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Payment\PaymentService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Payment\PaymentKind;
use App\Http\Core\Const\Payment\PaymentStatus;
use App\Http\Core\Const\Ride\BookingStatus;
use App\Http\Services\User\Payments\Logic\RideCardPaymentService;
use App\Models\EventOutbox;
use App\Models\LedgerPayment;
use App\Models\RideBooking;
use Illuminate\Support\Facades\DB;

class RideCardPaymentTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_23_000003_add_sub_service_id_to_ride_bookings.php',
    ];

    private function completedCardBooking(int $total = 10000): RideBooking
    {
        return RideBooking::query()->create([
            'user_id' => 7,
            'office_id' => 3,
            'driver_id' => 9,
            'source' => 'rider',
            'service' => 'ride',
            'service_class' => 'economy',
            'pricing_style' => 'meter',
            'status' => BookingStatus::COMPLETED,
            'pickup_lat' => 25.0, 'pickup_lng' => 51.0,
            'dropoff_lat' => 25.1, 'dropoff_lng' => 51.1,
            'currency_code' => 'USD',
            'fare_minor' => $total,
            'discount_minor' => 0,
            'total_minor' => $total,
            'held_minor' => 0,
            'payment_method' => 'card',
            'completed_at' => now(),
        ]);
    }

    public function test_card_ride_payment_distributes_the_fare_three_ways(): void
    {
        $booking = $this->completedCardBooking(10000);
        $payments = app(PaymentService::class);
        $wallet = new FleetWalletService(new LedgerService());
        $key = 'ridepay:' . $booking->id;

        $payments->createRideIntent(7, (int) $booking->id, 10000, 'USD', 'stripe', $key, 'pi_card_1');
        $payments->handleGatewayEvent($key, PaymentStatus::SUCCEEDED, 'pi_card_1');

        // Same split as any digital ride: fleet 5%, office 0%, driver the rest.
        $this->assertSame(9500, $wallet->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame(500, $wallet->revenueBalanceMinor('fleet', 0, 'USD'));

        $this->assertSame(1, (int) DB::table('commission_snapshots')->where('booking_id', $booking->id)->count());
        $this->assertSame(1, (int) EventOutbox::query()->where('type', EventType::RIDE_RELEASED)->count());
        $this->assertSame(PaymentStatus::SUCCEEDED, LedgerPayment::query()->where('idempotency_key', $key)->value('status'));
    }

    public function test_card_ride_settlement_is_idempotent(): void
    {
        $booking = $this->completedCardBooking(10000);
        $payments = app(PaymentService::class);
        $wallet = new FleetWalletService(new LedgerService());
        $key = 'ridepay:' . $booking->id;

        $payments->createRideIntent(7, (int) $booking->id, 10000, 'USD', 'stripe', $key, 'pi_card_2');
        $payments->handleGatewayEvent($key, PaymentStatus::SUCCEEDED, 'pi_card_2');
        $payments->handleGatewayEvent($key, PaymentStatus::SUCCEEDED, 'pi_card_2');

        $this->assertSame(9500, $wallet->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame(1, (int) DB::table('ledger_transactions')->where('kind', 'ride_release')->count());
        $this->assertSame(1, (int) EventOutbox::query()->where('type', EventType::RIDE_RELEASED)->count());
    }

    public function test_card_ride_settlement_keeps_the_ledger_balanced(): void
    {
        $booking = $this->completedCardBooking(7300);
        $payments = app(PaymentService::class);
        $key = 'ridepay:' . $booking->id;

        $payments->createRideIntent(7, (int) $booking->id, 7300, 'USD', 'stripe', $key, 'pi_card_3');
        $payments->handleGatewayEvent($key, PaymentStatus::SUCCEEDED, 'pi_card_3');

        $report = (new LedgerIntegrityService())->verify();
        $this->assertTrue($report['ok'], 'ledger integrity violations: ' . json_encode($report['violations']));
        $this->assertSame([], $report['violations']);
    }

    // ── pre-authorization (Uber-style hold at booking) ──────────────────────

    private function fakeGateway(string $authStatus = 'requires_capture', string $captureStatus = 'succeeded'): CardGateway
    {
        return new class($authStatus, $captureStatus) implements CardGateway {
            public bool $cancelled = false;
            public ?int $captured = null;

            public function __construct(private string $authStatus, private string $captureStatus)
            {
            }

            public function setupIntent(int $userId): array
            {
                return [];
            }

            public function describe(string $token): ?array
            {
                return null;
            }

            public function paymentIntent(int $userId, int $amountMinor, string $currency, ?int $paymentMethodId, string $idempotencyKey, array $metadata = [], bool $manualCapture = false): array
            {
                return ['id' => 'pi_' . $idempotencyKey, 'clientSecret' => 'secret', 'status' => 'requires_capture', 'requiresAction' => false];
            }

            public function capturePaymentIntent(string $paymentIntentId, int $amountToCaptureMinor): string
            {
                $this->captured = $amountToCaptureMinor;

                return $this->captureStatus;
            }

            public function cancelPaymentIntent(string $paymentIntentId): void
            {
                $this->cancelled = true;
            }

            public function paymentIntentStatus(string $paymentIntentId): ?string
            {
                return $this->authStatus;
            }
        };
    }

    private function service(CardGateway $gateway): RideCardPaymentService
    {
        return new RideCardPaymentService($gateway, app(PaymentService::class), app(TariffResolver::class), app(PricingService::class));
    }

    private function authorizedHold(int $userId, int $amountMinor, string $pi): LedgerPayment
    {
        return app(PaymentService::class)->createRideIntent($userId, null, $amountMinor, 'USD', 'stripe', 'ridecard:' . $pi, $pi);
    }

    public function test_capture_at_completion_settles_the_final_fare_from_the_hold(): void
    {
        $booking = $this->completedCardBooking(10000);
        $booking->stripe_payment_intent_id = 'pi_auth1';
        $booking->save();
        $this->authorizedHold(7, 12000, 'pi_auth1'); // authorised more than the final fare

        $gateway = $this->fakeGateway();
        $this->service($gateway)->captureForBooking($booking);

        // Captured the FINAL fare (10000), not the larger hold (12000).
        $this->assertSame(10000, $gateway->captured);
        // Settled three-ways: fleet 5%, driver the rest.
        $wallet = new FleetWalletService(new LedgerService());
        $this->assertSame(9500, $wallet->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame(1, (int) DB::table('commission_snapshots')->where('booking_id', $booking->id)->count());
        $this->assertSame(PaymentStatus::SUCCEEDED, LedgerPayment::query()->where('provider_ref', 'pi_auth1')->value('status'));
    }

    public function test_failed_capture_leaves_the_trip_unsettled_for_the_fallback(): void
    {
        $booking = $this->completedCardBooking(10000);
        $booking->stripe_payment_intent_id = 'pi_auth2';
        $booking->save();
        $this->authorizedHold(7, 10000, 'pi_auth2');

        $this->service($this->fakeGateway(captureStatus: 'requires_payment_method'))->captureForBooking($booking);

        // Nothing settled — the rider can still pay via the fallback.
        $this->assertSame(0, (int) DB::table('commission_snapshots')->where('booking_id', $booking->id)->count());
        $this->assertSame(PaymentStatus::PENDING, LedgerPayment::query()->where('provider_ref', 'pi_auth2')->value('status'));
    }

    public function test_release_voids_an_uncaptured_hold_on_cancel(): void
    {
        $booking = $this->completedCardBooking(10000);
        $booking->stripe_payment_intent_id = 'pi_auth3';
        $booking->save();
        $this->authorizedHold(7, 10000, 'pi_auth3');

        $gateway = $this->fakeGateway();
        $this->service($gateway)->release($booking);

        $this->assertTrue($gateway->cancelled);
        $this->assertSame(PaymentStatus::FAILED, LedgerPayment::query()->where('provider_ref', 'pi_auth3')->value('status'));
    }

    public function test_attach_rejects_a_hold_that_is_too_small(): void
    {
        $booking = $this->completedCardBooking(10000);
        $this->authorizedHold(7, 9000, 'pi_small'); // hold < fare

        $this->expectException(\App\Http\Core\Exceptions\DomainException::class);
        $this->service($this->fakeGateway())->attachAuthorization(7, $booking, 'pi_small');
    }
}
