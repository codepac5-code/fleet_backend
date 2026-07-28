<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerIntegrityService;
use App\Http\Core\Classes\Ledger\LedgerService;

/**
 * The commission model chosen by the operator: the driver keeps the full fare,
 * and the fleet/office commission is DEBITED from the driver's prepaid wallet —
 * wallet-first, remainder to dues. Fleet always takes its share; the office
 * takes its share only for a driver that belongs to an office.
 */
class CommissionChargeTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000016_create_payout_requests_table.php',
    ];

    private FleetWalletService $wallet;
    private LedgerIntegrityService $verifier;
    private string $cur = 'USD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = new FleetWalletService(new LedgerService());
        $this->verifier = new LedgerIntegrityService();
    }

    /** Fund a driver wallet the way real earnings do (not a USER top-up). */
    private function fundDriverWallet(int $driverId, int $amount, string $key): void
    {
        $this->wallet->adjustment([
            ['owner_type' => 'fleet', 'owner_id' => 0, 'account_type' => 'psp_clearing', 'direction' => 'debit', 'amount_minor' => $amount],
            ['owner_type' => 'driver', 'owner_id' => $driverId, 'account_type' => 'wallet', 'direction' => 'credit', 'amount_minor' => $amount],
        ], $this->cur, $key);
    }

    public function test_office_driver_commission_debits_wallet_to_fleet_and_office(): void
    {
        $this->fundDriverWallet(9, 10000, 'fund-9');

        // fare 10000, fleet 12%, office 18% → 1200 fleet + 1800 office = 3000.
        $this->wallet->chargeCommission([
            'booking_id' => 7001, 'driver_id' => 9, 'office_id' => 3,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ]);

        $this->assertSame(7000, $this->wallet->walletBalanceMinor('driver', 9, $this->cur));
        $this->assertSame(1200, $this->wallet->revenueBalanceMinor('fleet', 0, $this->cur));
        $this->assertSame(1800, $this->wallet->walletBalanceMinor('office', 3, $this->cur)); // office share → wallet
        $this->assertSame(0, $this->wallet->duesBalanceMinor(9, $this->cur));
        $this->assertTrue($this->verifier->verify()['ok']);
    }

    public function test_fleet_direct_driver_pays_only_fleet_share(): void
    {
        $this->fundDriverWallet(9, 10000, 'fund-9');

        // office_id 0 → fleet-direct driver: office share must be ignored entirely.
        $this->wallet->chargeCommission([
            'booking_id' => 7002, 'driver_id' => 9, 'office_id' => 0,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ]);

        $this->assertSame(8800, $this->wallet->walletBalanceMinor('driver', 9, $this->cur));
        $this->assertSame(1200, $this->wallet->revenueBalanceMinor('fleet', 0, $this->cur));
        $this->assertSame(0, $this->wallet->walletBalanceMinor('office', 3, $this->cur));
        $this->assertSame(0, $this->wallet->duesBalanceMinor(9, $this->cur));
    }

    public function test_empty_wallet_records_full_commission_as_dues(): void
    {
        // Cash trip, wallet unfunded → the whole commission becomes debt.
        $this->wallet->chargeCommission([
            'booking_id' => 7003, 'driver_id' => 9, 'office_id' => 3,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ]);

        $this->assertSame(0, $this->wallet->walletBalanceMinor('driver', 9, $this->cur));
        $this->assertSame(3000, $this->wallet->duesBalanceMinor(9, $this->cur));
        $this->assertSame(1200, $this->wallet->revenueBalanceMinor('fleet', 0, $this->cur));
        $this->assertSame(1800, $this->wallet->walletBalanceMinor('office', 3, $this->cur));
        $this->assertTrue($this->verifier->verify()['ok']);
    }

    public function test_partial_wallet_splits_between_wallet_and_dues(): void
    {
        $this->fundDriverWallet(9, 2000, 'fund-9'); // covers only part of 3000 commission

        $this->wallet->chargeCommission([
            'booking_id' => 7004, 'driver_id' => 9, 'office_id' => 3,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ]);

        $this->assertSame(0, $this->wallet->walletBalanceMinor('driver', 9, $this->cur));
        $this->assertSame(1000, $this->wallet->duesBalanceMinor(9, $this->cur)); // 3000 - 2000
        $this->assertSame(1200, $this->wallet->revenueBalanceMinor('fleet', 0, $this->cur));
        $this->assertSame(1800, $this->wallet->walletBalanceMinor('office', 3, $this->cur));
        $this->assertTrue($this->verifier->verify()['ok']);
    }

    public function test_digital_distributes_from_fleet_to_wallets(): void
    {
        // Customer paid via PSP → fleet distributes: driver net + office share to
        // their wallets, fleet keeps its share as revenue. No driver-wallet debit.
        $this->wallet->distributeDigital([
            'booking_id' => 8001, 'driver_id' => 9, 'office_id' => 3,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ]);

        $this->assertSame(7000, $this->wallet->walletBalanceMinor('driver', 9, $this->cur)); // net
        $this->assertSame(1800, $this->wallet->walletBalanceMinor('office', 3, $this->cur));
        $this->assertSame(1200, $this->wallet->revenueBalanceMinor('fleet', 0, $this->cur));
        $this->assertSame(0, $this->wallet->duesBalanceMinor(9, $this->cur));
        $this->assertTrue($this->verifier->verify()['ok']);
    }

    public function test_digital_fleet_direct_driver_gets_all_but_fleet_share(): void
    {
        $this->wallet->distributeDigital([
            'booking_id' => 8002, 'driver_id' => 9, 'office_id' => 0,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ]);

        $this->assertSame(8800, $this->wallet->walletBalanceMinor('driver', 9, $this->cur));
        $this->assertSame(0, $this->wallet->walletBalanceMinor('office', 3, $this->cur));
        $this->assertSame(1200, $this->wallet->revenueBalanceMinor('fleet', 0, $this->cur));
    }

    public function test_withdrawal_cascade_driver_to_office_to_fleet(): void
    {
        // Driver earns 7000 net digitally, office earns 1800.
        $this->wallet->distributeDigital([
            'booking_id' => 8003, 'driver_id' => 9, 'office_id' => 3,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ]);

        // Leg 1: driver withdraws 7000 at the office → moves to office wallet.
        $this->wallet->withdrawDriverToOffice(9, 3, 7000, $this->cur, 'wd-drv-8003');
        $this->assertSame(0, $this->wallet->walletBalanceMinor('driver', 9, $this->cur));
        $this->assertSame(8800, $this->wallet->walletBalanceMinor('office', 3, $this->cur)); // 1800 + 7000

        // Leg 2: office withdraws its full 8800 from the fleet (existing payout).
        $req = new \App\Http\Core\Classes\Payment\PayoutService($this->wallet);
        $r = $req->request('office', 3, 'wallet', 8800, $this->cur);
        $req->pay($r->id);
        $this->assertSame(0, $this->wallet->walletBalanceMinor('office', 3, $this->cur));
        $this->assertTrue($this->verifier->verify()['ok']);
    }

    public function test_commission_is_idempotent_per_booking(): void
    {
        $this->fundDriverWallet(9, 10000, 'fund-9');

        $args = [
            'booking_id' => 7005, 'driver_id' => 9, 'office_id' => 3,
            'currency_code' => $this->cur, 'fare_minor' => 10000,
            'fleet_rate' => 12.0, 'office_rate' => 18.0,
        ];
        $this->wallet->chargeCommission($args);
        $this->wallet->chargeCommission($args);

        // Charged exactly once.
        $this->assertSame(7000, $this->wallet->walletBalanceMinor('driver', 9, $this->cur));
        $this->assertSame(1200, $this->wallet->revenueBalanceMinor('fleet', 0, $this->cur));
    }
}
