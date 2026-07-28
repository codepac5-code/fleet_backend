<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Auth\RiderProvisioningService;
use App\Http\Core\Classes\Dispatch\DispatchService;
use App\Http\Core\Classes\Dispatch\DriverLocationStore;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\BookingSettlementService;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Classes\Ride\OfficeBookingService;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Const\Dispatch\DispatchStatus;
use App\Http\Core\Const\Dispatch\PresenceStatus;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Ride\BookingSource;
use App\Http\Core\Exceptions\DomainException;
use App\Models\CommissionSnapshot;
use App\Models\DispatchJob;
use App\Models\DispatchOffer;
use App\Models\DriverPresence;
use App\Models\RideBooking;
use App\Models\ServiceTariff;
use App\Models\SiteSetting;
use App\Models\User;

class OfficeBookingTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_01_000006_create_site_settings_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000004_create_driver_presence_table.php',
        '2026_07_13_000003_add_busy_reason_to_driver_presence_table.php',
        '2026_06_25_000005_create_dispatch_jobs_table.php',
        '2026_06_25_000006_create_dispatch_offers_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
        '2026_07_11_000001_create_ride_bookings_table.php',
        '2026_07_11_000004_add_titles_to_ride_bookings_table.php',
        '2026_07_11_000008_add_schedule_to_ride_bookings_table.php',
        '2026_07_11_000009_add_change_revision_to_ride_bookings_table.php',
        '2026_07_14_000001_add_office_booking_fields_to_ride_bookings.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2024_10_23_085910_create_users_table.php',
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_07_13_000006_add_billing_lifecycle_to_office_subscriptions.php',
    ];

    private function service(): OfficeBookingService
    {
        return new OfficeBookingService(
            new RiderProvisioningService(),
            new TariffResolver(),
            new PricingService(),
            new DispatchService(new EventBus()),
            new FleetWalletService(new LedgerService()),
            new \App\Http\Core\Repositories\Ride\EloquentRideBookingRepository(),
            new EventBus()
        );
    }

    private function fundOffice(int $officeId, int $amount): void
    {
        (new FleetWalletService(new LedgerService()))->adjustment([
            ['owner_type' => OwnerType::FLEET, 'owner_id' => 0, 'account_type' => AccountType::REVENUE, 'direction' => Direction::DEBIT, 'amount_minor' => $amount],
            ['owner_type' => OwnerType::OFFICE, 'owner_id' => $officeId, 'account_type' => AccountType::WALLET, 'direction' => Direction::CREDIT, 'amount_minor' => $amount],
        ], 'USD', 'fund-office:' . $officeId);
    }

    private function seedTariff(int $office = 3, string $style = 'fixed', int $fixed = 5000): void
    {
        ServiceTariff::query()->create([
            'office_id' => $office,
            'service' => 'ride',
            'service_class' => 'standard',
            'currency_code' => 'USD',
            'pricing_style' => $style,
            'base_minor' => 500, 'per_km_minor' => 200, 'per_minute_minor' => 30,
            'minimum_minor' => 1000, 'fixed_minor' => $fixed,
        ]);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'office_id' => 3,
            'phone' => '+97455123456',
            'name' => 'Sara Ali',
            'service' => 'ride',
            'service_class' => 'standard',
            'pickup' => ['lat' => 25.2854, 'lng' => 51.5310, 'title' => 'Souq'],
            'dropoff' => ['lat' => 25.2700, 'lng' => 51.6000, 'title' => 'Airport'],
        ], $override);
    }

    public function test_creates_manual_cash_booking_with_direct_assign(): void
    {
        $this->seedTariff();

        $result = $this->service()->create($this->payload([
            'fare_minor' => 8000,
            'assign' => ['mode' => 'driver', 'driver_id' => 55],
        ]), 'office:3');

        $this->assertSame('assigned', $result['status']);
        $this->assertSame(55, $result['assigned_driver_id']);
        $this->assertSame(8000, $result['total_minor']);

        $booking = RideBooking::query()->find($result['booking_id']);
        $this->assertSame(BookingSource::OFFICE, $booking->source);
        $this->assertSame('manual', $booking->pricing_style);
        $this->assertSame('cash', $booking->payment_method);
        $this->assertSame(0, (int) $booking->held_minor);
        $this->assertSame('office:3', $booking->created_by);

        $job = DispatchJob::query()->where('booking_id', $result['booking_id'])->first();
        $this->assertSame(55, (int) $job->assigned_driver_id);
        $this->assertSame(DispatchStatus::ASSIGNED, $job->status);
    }

    public function test_auto_prices_from_tariff_when_no_manual_fare(): void
    {
        $this->seedTariff(3, 'fixed', 6500);

        $result = $this->service()->create($this->payload([
            'assign' => ['mode' => 'driver', 'driver_id' => 9],
        ]), 'admin:1');

        $this->assertSame(6500, $result['total_minor']);
    }

    public function test_finds_existing_customer_by_phone(): void
    {
        $this->seedTariff();
        $existing = User::query()->create([
            'firstName' => 'Old', 'lastName' => 'Customer', 'phoneNumber' => '+97455123456',
            'dialCode' => '+974', 'password' => 'x', 'isActive' => 1,
        ]);

        $result = $this->service()->create($this->payload([
            'fare_minor' => 4000, 'assign' => ['mode' => 'driver', 'driver_id' => 9],
        ]), 'office:3');

        $this->assertSame((int) $existing->id, $result['customer_id']);
        $this->assertSame(1, User::query()->where('phoneNumber', '+97455123456')->count());
    }

    /**
     * Broadcast dispatch offers the ride to nearby online drivers.
     *
     * Candidate selection is TWO-SOURCE: DispatchService::findCandidates asks
     * Redis (DriverLocationStore geo index) who is physically in range, and only
     * then gates that set against DriverPresence for office + ONLINE status.
     * Seeding the presence row alone is therefore not enough — Redis returns no
     * one, and the dispatcher correctly finds zero candidates.
     *
     * DriverLocationStore degrades to "nobody nearby" when Redis is unreachable
     * (a deliberate choice so a Redis outage cannot fail a dispatch tick), which
     * means this test cannot be made hermetic without a fake for that store. It
     * seeds the geo index and skips when Redis is absent, so CI without Redis
     * stays green while a local run still exercises the real dispatch path.
     */
    public function test_broadcast_mode_offers_nearby_online_driver(): void
    {
        $this->seedTariff();
        DriverPresence::query()->create([
            'driver_id' => 71, 'office_id' => 3, 'status' => PresenceStatus::ONLINE,
            'lat' => 25.2850, 'lng' => 51.5312, 'heartbeat_at' => now(),
        ]);

        $region = ShardManager::shardKey();
        DriverLocationStore::put($region, 71, 25.2850, 51.5312);

        if (DriverLocationStore::search($region, 25.2850, 51.5312, 5000) === []) {
            $this->markTestSkipped('dispatch candidate search needs a reachable Redis (DriverLocationStore)');
        }

        $result = $this->service()->create($this->payload([
            'fare_minor' => 5000, 'assign' => ['mode' => 'broadcast'],
        ]), 'office:3');

        $this->assertSame('matching', $result['status']);
        $this->assertGreaterThanOrEqual(1, DispatchOffer::query()->where('booking_id', $result['booking_id'])->count());
    }

    public function test_office_wallet_booking_holds_from_office_balance(): void
    {
        $this->seedTariff();
        $this->fundOffice(3, 20000);
        $wallet = new FleetWalletService(new LedgerService());

        $result = $this->service()->create($this->payload([
            'fare_minor' => 8000, 'payment_method' => 'office_wallet',
            'assign' => ['mode' => 'driver', 'driver_id' => 9],
        ]), 'office:3');

        $this->assertSame('office_wallet', $result['payment_method']);
        $this->assertSame(12000, $wallet->walletBalanceMinor(OwnerType::OFFICE, 3, 'USD'));
        $this->assertSame(8000, $wallet->escrowBalanceMinor($result['booking_id'], 'USD'));

        $booking = RideBooking::query()->find($result['booking_id']);
        $this->assertSame(8000, (int) $booking->held_minor);
    }

    public function test_office_wallet_insufficient_balance_rolls_back(): void
    {
        $this->seedTariff();
        $this->fundOffice(3, 3000);

        try {
            $this->service()->create($this->payload([
                'fare_minor' => 8000, 'payment_method' => 'office_wallet',
                'assign' => ['mode' => 'driver', 'driver_id' => 9],
            ]), 'office:3');
            $this->fail('expected insufficient balance');
        } catch (DomainException $e) {
            $this->assertStringContainsString('insufficient', $e->getMessage());
        }

        $this->assertSame(0, RideBooking::query()->count());
        $this->assertSame(3000, (new FleetWalletService(new LedgerService()))->walletBalanceMinor(OwnerType::OFFICE, 3, 'USD'));
    }

    public function test_office_wallet_escrow_refunds_to_office(): void
    {
        $wallet = new FleetWalletService(new LedgerService());
        $this->fundOffice(3, 10000);

        $wallet->holdRideFromOffice(4200, 3, 7000, 'USD', 'office-hold:4200');
        $this->assertSame(3000, $wallet->walletBalanceMinor(OwnerType::OFFICE, 3, 'USD'));

        $wallet->refundEscrowToOffice(4200, 3, 7000, 'USD', 'cancel-refund:4200');
        $this->assertSame(10000, $wallet->walletBalanceMinor(OwnerType::OFFICE, 3, 'USD'));
        $this->assertSame(0, $wallet->escrowBalanceMinor(4200, 'USD'));
    }

    public function test_office_booking_commission_override_applies_at_settlement(): void
    {
        SiteSetting::put('office_booking_fleet_rate', 5);

        $settle = new BookingSettlementService(
            new FleetWalletService(new LedgerService()),
            new CommissionResolver(new OfficeSubscriptionService())
        );

        $settle->settleCash([
            'booking_id' => 4100, 'office_id' => 3, 'driver_id' => 22,
            'currency_code' => 'USD', 'total_minor' => 10000, 'fare_minor' => 10000,
            'discount_minor' => 0, 'pricing_style' => 'manual', 'source' => BookingSource::OFFICE,
        ]);

        $snap = CommissionSnapshot::query()->where('booking_id', 4100)->first();
        $this->assertSame(5.0, (float) $snap->fleet_rate);
    }
}
