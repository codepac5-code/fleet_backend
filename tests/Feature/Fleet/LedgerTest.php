<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ledger\Money;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Http\Core\Const\Ledger\OwnerType;
use Illuminate\Support\Facades\DB;

class LedgerTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
    ];

    private FleetWalletService $wallet;
    private LedgerService $ledger;
    private string $cur = 'USD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->ledger = new LedgerService();
        $this->wallet = new FleetWalletService($this->ledger);
    }

    public function test_topup_is_idempotent(): void
    {
        $this->wallet->topUp(7, Money::toMinor(100), $this->cur, 'tu-1');
        $this->wallet->topUp(7, Money::toMinor(100), $this->cur, 'tu-1');
        $this->assertSame(10000, $this->wallet->walletBalanceMinor(OwnerType::USER, 7, $this->cur));
    }

    public function test_hold_then_three_way_release_with_snapshot(): void
    {
        $total = Money::toMinor(49.50);
        $this->wallet->topUp(7, $total, $this->cur, 'tu');
        $this->wallet->holdRide(1001, 7, $total, $this->cur, 'hold');
        $this->assertSame($total, $this->ledger->ownerBalanceMinor(OwnerType::BOOKING, 1001, AccountType::ESCROW, $this->cur));

        $this->wallet->releaseRide([
            'booking_id' => 1001, 'office_id' => 3, 'driver_id' => 9,
            'currency_code' => $this->cur, 'total_minor' => $total,
            'fleet_rate' => 12, 'office_rate' => 18, 'pricing_style' => 'fixed', 'subscription_plan' => 'business',
        ]);

        $this->assertSame(0, $this->ledger->ownerBalanceMinor(OwnerType::BOOKING, 1001, AccountType::ESCROW, $this->cur));
        $this->assertSame(3465, $this->wallet->walletBalanceMinor(OwnerType::DRIVER, 9, $this->cur));
        $this->assertSame(891, $this->wallet->walletBalanceMinor(OwnerType::OFFICE, 3, $this->cur)); // office share → wallet
        $this->assertSame(594, $this->wallet->revenueBalanceMinor(OwnerType::FLEET, OwnerType::FLEET_OWNER_ID, $this->cur));

        $snap = DB::table('commission_snapshots')->where('booking_id', 1001)->first();
        $this->assertSame(4950, (int) $snap->total_minor);
        $this->assertSame(3465, (int) $snap->driver_minor);
    }

    public function test_cash_trip_loads_driver_dues_then_settle(): void
    {
        $total = Money::toMinor(49.50);
        $this->wallet->cashCommission([
            'booking_id' => 1002, 'office_id' => 3, 'driver_id' => 9,
            'currency_code' => $this->cur, 'total_minor' => $total, 'fleet_rate' => 12, 'office_rate' => 18,
        ]);
        $this->assertSame(1485, $this->wallet->duesBalanceMinor(9, $this->cur));

        // Fund the DRIVER's wallet (a driver wallet is funded by card-trip
        // earnings, not by a USER top-up), then settle the dues from it.
        $this->wallet->adjustment([
            ['owner_type' => OwnerType::FLEET, 'owner_id' => OwnerType::FLEET_OWNER_ID, 'account_type' => AccountType::PSP_CLEARING, 'direction' => Direction::DEBIT, 'amount_minor' => 1485],
            ['owner_type' => OwnerType::DRIVER, 'owner_id' => 9, 'account_type' => AccountType::WALLET, 'direction' => Direction::CREDIT, 'amount_minor' => 1485],
        ], $this->cur, 'fund-driver-wallet');
        $this->wallet->settleDuesFromWallet(9, 1485, $this->cur, 'settle');
        $this->assertSame(0, $this->wallet->duesBalanceMinor(9, $this->cur));
    }

    public function test_split_remainder_is_exact_for_odd_totals(): void
    {
        $split = $this->wallet->splitThreeWay(10001, 12, 18);
        $this->assertSame(10001, $split['fleet'] + $split['office'] + $split['driver']);
        $this->assertSame(7001, $split['driver']);
    }

    public function test_global_invariant_debit_equals_credit(): void
    {
        $total = Money::toMinor(50);
        $this->wallet->topUp(7, $total, $this->cur, 'tu');
        $this->wallet->holdRide(1003, 7, $total, $this->cur, 'h');
        $this->wallet->releaseRide([
            'booking_id' => 1003, 'office_id' => 3, 'driver_id' => 9,
            'currency_code' => $this->cur, 'total_minor' => $total, 'fleet_rate' => 12, 'office_rate' => 18,
        ]);

        $debit = (int) DB::table('ledger_entries')->where('direction', 'debit')->sum('amount_minor');
        $credit = (int) DB::table('ledger_entries')->where('direction', 'credit')->sum('amount_minor');
        $this->assertSame($debit, $credit);
    }
}
