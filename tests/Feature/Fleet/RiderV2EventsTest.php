<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Payment\PaymentService;
use App\Http\Core\Classes\Rating\RatingService;
use App\Http\Core\Classes\Ride\BookingChatService;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Dispatch\OfferStatus;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Models\DispatchJob;
use App\Models\DispatchOffer;
use App\Models\DriverPresence;
use App\Models\EventOutbox;
use App\Models\RideBooking;

class RiderV2EventsTest extends FleetTestCase
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
        '2026_06_25_000017_create_ride_ratings_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000005_create_booking_chat_messages_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
    ];

    private function booking(string $status = 'matching'): RideBooking
    {
        return RideBooking::query()->create([
            'user_id' => 7, 'office_id' => 3, 'source' => 'rider', 'service' => 'ride', 'service_class' => 'standard',
            'pricing_style' => 'fixed', 'status' => $status, 'pickup_lat' => 25.28, 'pickup_lng' => 51.53,
            'dropoff_lat' => 25.27, 'dropoff_lng' => 51.6, 'distance_m' => 5000, 'duration_s' => 600,
            'currency_code' => 'USD', 'fare_minor' => 5000, 'total_minor' => 5000, 'payment_method' => 'cash',
        ]);
    }

    private function outbox(string $type): ?EventOutbox
    {
        return EventOutbox::query()->where('type', $type)->first();
    }

    public function test_ride_assigned_includes_user_channel(): void
    {
        $b = $this->booking();
        DispatchJob::query()->create(['booking_id' => $b->id, 'office_id' => 3, 'service_class' => 'standard', 'lat' => 25.28, 'lng' => 51.53, 'status' => DispatchStatus::OFFERED, 'wave' => 1]);
        DispatchOffer::query()->create(['booking_id' => $b->id, 'driver_id' => 101, 'wave' => 1, 'status' => OfferStatus::OFFERED, 'distance_m' => 100, 'expires_at' => now()->addMinute()]);
        DriverPresence::query()->create(['driver_id' => 101, 'office_id' => 3, 'status' => PresenceStatus::ONLINE, 'lat' => 25.28, 'lng' => 51.53, 'heartbeat_at' => now()]);

        app(DispatchService::class)->accept((int) $b->id, 101);

        $event = $this->outbox('dispatch.ride_assigned');
        $this->assertNotNull($event);
        $this->assertContains('user.7', $event->channels);
        $this->assertContains('booking.' . $b->id, $event->channels);
        $this->assertSame(101, (int) RideBooking::query()->find($b->id)->driver_id);
    }

    public function test_wallet_credited_and_payment_succeeded_on_settlement(): void
    {
        $payments = app(PaymentService::class);
        $payments->createTopUpIntent(7, 5000, 'USD', 'stripe', 'topup-key-1');
        $payments->handleGatewayEvent('topup-key-1', 'succeeded', 'pi_abc');

        $credited = $this->outbox('wallet.credited');
        $this->assertNotNull($credited);
        $this->assertContains('user.7', $credited->channels);
        $this->assertSame(5000, $credited->payload['amount']);
        $this->assertSame(5000, $credited->payload['balance_after']);
        $this->assertSame('USD', $credited->payload['currency']);

        $paid = $this->outbox('payment.succeeded');
        $this->assertNotNull($paid);
        $this->assertContains('user.7', $paid->channels);
        $this->assertSame('stripe', $paid->payload['method']);
    }

    public function test_rating_received_includes_from_role(): void
    {
        app(RatingService::class)->rate(55, 'user', 7, 'driver', 101, 5, 'great');

        $event = $this->outbox('rating.received');
        $this->assertNotNull($event);
        $this->assertContains('driver.101', $event->channels);
        $this->assertSame('user', $event->payload['from_role']);
        $this->assertSame(5, $event->payload['stars']);
    }

    public function test_booking_chat_message_has_sender_and_text(): void
    {
        $b = $this->booking('on_trip');
        DispatchJob::query()->create(['booking_id' => $b->id, 'office_id' => 3, 'service_class' => 'standard', 'lat' => 25.28, 'lng' => 51.53, 'status' => DispatchStatus::ASSIGNED, 'assigned_driver_id' => 101, 'assigned_at' => now(), 'wave' => 1]);

        app(BookingChatService::class)->send((int) $b->id, BookingChatService::RIDER, 'On my way');

        $event = $this->outbox('booking.chat_message');
        $this->assertNotNull($event);
        $this->assertSame('On my way', $event->payload['text']);
        $this->assertSame('rider', $event->payload['sender']);
        $this->assertSame('rider', $event->payload['sender_role']);
    }
}
