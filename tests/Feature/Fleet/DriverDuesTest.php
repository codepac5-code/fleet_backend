<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\DriverDuesService;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DriverDuesTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
    ];

    private FleetWalletService $wallet;
    private DriverDuesService $dues;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = new FleetWalletService(new LedgerService());
        $this->dues = new DriverDuesService($this->wallet);
    }

    private function createDues(int $booking = 6001): void
    {
        $this->wallet->cashCommission([
            'booking_id' => $booking, 'office_id' => 3, 'driver_id' => 9, 'currency_code' => 'USD',
            'total_minor' => 10000, 'fleet_rate' => 18.0, 'office_rate' => 0.0,
        ]);
    }

    private function fundDriverWallet(int $booking = 5001): void
    {
        $this->wallet->topUp(7, 10000, 'USD', 'fund:' . $booking);
        $this->wallet->holdRide($booking, 7, 10000, 'USD', 'hold:' . $booking);
        $this->wallet->releaseRide([
            'booking_id' => $booking, 'office_id' => 3, 'driver_id' => 9, 'currency_code' => 'USD',
            'total_minor' => 10000, 'fleet_rate' => 18.0, 'office_rate' => 0.0,
        ]);
    }

    public function test_settle_full_dues_from_wallet(): void
    {
        $this->createDues();
        $this->fundDriverWallet();

        $this->assertSame(1800, $this->dues->outstanding(9, 'USD'));
        $this->assertSame(8200, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));

        $result = $this->dues->settleFromWallet(9, null, 'USD', 'settle-1');

        $this->assertSame(1800, $result['settled_minor']);
        $this->assertSame(0, $result['remaining_dues_minor']);
        $this->assertSame(6400, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame(1, (int) DB::table('ledger_transactions')->where('kind', 'dues_settle')->count());
    }

    public function test_partial_settlement(): void
    {
        $this->createDues();
        $this->fundDriverWallet();

        $result = $this->dues->settleFromWallet(9, 1000, 'USD', 'settle-2');

        $this->assertSame(1000, $result['settled_minor']);
        $this->assertSame(800, $result['remaining_dues_minor']);
        $this->assertSame(7200, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));
    }

    public function test_no_dues_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no_dues');

        $this->dues->settleFromWallet(9, null, 'USD', 'settle-3');
    }

    public function test_insufficient_wallet_throws(): void
    {
        $this->createDues();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('insufficient_balance');

        $this->dues->settleFromWallet(9, null, 'USD', 'settle-4');
    }
}
