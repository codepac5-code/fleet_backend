<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\BookingSettlementService;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ride\RideLifecycleService;
use App\Http\Core\Classes\Subscription\CommissionResolver;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Const\Event\EventType;
use App\Models\EventOutbox;
use Illuminate\Support\Facades\DB;

class RideLifecycleTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
    ];

    private FleetWalletService $wallet;
    private RideLifecycleService $rides;

    protected function setUp(): void
    {
        parent::setUp();
        $ledger = new LedgerService();
        $this->wallet = new FleetWalletService($ledger);
        $settlement = new BookingSettlementService($this->wallet, new CommissionResolver(new OfficeSubscriptionService()));
        $this->rides = new RideLifecycleService($settlement, new EventBus());
    }

    private function booking(int $bookingId = 5001, int $total = 10000): array
    {
        return [
            'booking_id' => $bookingId,
            'office_id' => 3,
            'driver_id' => 9,
            'currency_code' => 'USD',
            'total_minor' => $total,
        ];
    }

    public function test_digital_settlement_splits_three_ways_and_emits_event(): void
    {
        // Digital = PSP → fleet distributes to wallets. No customer escrow.
        $this->rides->settle($this->booking(), 'digital');

        $this->assertSame(8200, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame(1800, $this->wallet->revenueBalanceMinor('fleet', 0, 'USD'));
        $this->assertSame(0, $this->wallet->revenueBalanceMinor('office', 3, 'USD'));

        $this->assertSame(1, (int) DB::table('commission_snapshots')->where('booking_id', 5001)->count());
        $this->assertSame(1, (int) EventOutbox::query()->where('type', EventType::RIDE_RELEASED)->count());
    }

    public function test_cash_settlement_charges_driver_dues(): void
    {
        $this->rides->settle($this->booking(6001, 10000), 'cash');

        $this->assertSame(1800, $this->wallet->duesBalanceMinor(9, 'USD'));
        $this->assertSame(1800, $this->wallet->revenueBalanceMinor('fleet', 0, 'USD'));
    }

    public function test_settlement_is_idempotent_including_the_event(): void
    {
        $this->rides->settle($this->booking(), 'digital');
        $this->rides->settle($this->booking(), 'digital');
        $this->rides->settle($this->booking(), 'digital');

        $this->assertSame(8200, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame(1, (int) DB::table('ledger_transactions')->where('kind', 'ride_release')->count());
        $this->assertSame(1, (int) EventOutbox::query()->where('type', EventType::RIDE_RELEASED)->count());
    }
}
