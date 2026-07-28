<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Pricing\PricingService;
use App\Http\Core\Classes\Pricing\TariffResolver;
use App\Http\Core\Classes\Ride\BookingHoldService;
use App\Models\ServiceTariff;
use RuntimeException;

class BookingHoldTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_07_01_000002_create_service_tariffs_table.php',
    ];

    private FleetWalletService $wallet;
    private BookingHoldService $holds;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = new FleetWalletService(new LedgerService());
        $this->holds = new BookingHoldService(new TariffResolver(), new PricingService(), $this->wallet);

        ServiceTariff::query()->create([
            'office_id' => 3,
            'service_class' => 'standard',
            'currency_code' => 'USD',
            'pricing_style' => 'meter',
            'base_minor' => 500,
            'per_km_minor' => 200,
            'per_minute_minor' => 30,
            'minimum_minor' => 1000,
        ]);
    }

    private function fund(int $amount): void
    {
        $this->wallet->topUp(7, $amount, 'USD', 'fund:' . $amount, 'test', 1);
    }

    public function test_hold_moves_fare_from_wallet_to_escrow(): void
    {
        $this->fund(5000);

        $result = $this->holds->hold(5001, 7, 3, 'standard', 3000, 600);

        $this->assertSame(1400, $result['held_minor']);
        $this->assertFalse($result['already_held']);
        $this->assertSame(1400, $this->wallet->escrowBalanceMinor(5001, 'USD'));
        $this->assertSame(3600, $this->wallet->walletBalanceMinor('user', 7, 'USD'));
    }

    public function test_hold_is_idempotent(): void
    {
        $this->fund(5000);

        $this->holds->hold(5001, 7, 3, 'standard', 3000, 600);
        $second = $this->holds->hold(5001, 7, 3, 'standard', 3000, 600);

        $this->assertTrue($second['already_held']);
        $this->assertSame(1400, $this->wallet->escrowBalanceMinor(5001, 'USD'));
        $this->assertSame(3600, $this->wallet->walletBalanceMinor('user', 7, 'USD'));
    }

    public function test_hold_rejects_insufficient_balance(): void
    {
        $this->fund(500);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('insufficient_balance');

        $this->holds->hold(5001, 7, 3, 'standard', 3000, 600);
    }

    public function test_hold_rejects_missing_tariff(): void
    {
        $this->fund(5000);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tariff_not_found');

        $this->holds->hold(5001, 7, 999, 'standard', 3000, 600);
    }
}
