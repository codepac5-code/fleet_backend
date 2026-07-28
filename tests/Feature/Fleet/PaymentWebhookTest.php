<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ledger\Money;
use App\Http\Core\Classes\Payment\PaymentService;
use App\Http\Core\Const\Options\PaymentGatewayName;
use App\Http\Core\Const\Payment\PaymentStatus;
use Illuminate\Support\Facades\DB;

class PaymentWebhookTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
    ];

    private PaymentService $pay;
    private FleetWalletService $wallet;
    private string $cur = 'USD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = new FleetWalletService(new LedgerService());
        $this->pay = new PaymentService($this->wallet);
    }

    public function test_webhook_credits_wallet_exactly_once(): void
    {
        $this->pay->createTopUpIntent(7, Money::toMinor(100), $this->cur, PaymentGatewayName::$STRIPE, 'k1', 'ch_1');
        $this->assertSame(0, $this->wallet->walletBalanceMinor('user', 7, $this->cur));

        $this->pay->handleGatewayEvent('k1', PaymentStatus::SUCCEEDED, 'ch_1');
        $this->pay->handleGatewayEvent('k1', PaymentStatus::SUCCEEDED, 'ch_1');
        $this->pay->handleGatewayEvent('k1', PaymentStatus::SUCCEEDED, 'ch_1');

        $this->assertSame(10000, $this->wallet->walletBalanceMinor('user', 7, $this->cur));
        $this->assertSame(1, (int) DB::table('ledger_transactions')->where('kind', 'topup')->count());
    }

    public function test_duplicate_intent_same_key_is_one_row(): void
    {
        $this->pay->createTopUpIntent(7, Money::toMinor(100), $this->cur, PaymentGatewayName::$STRIPE, 'k2', 'ch_2');
        $this->pay->createTopUpIntent(7, Money::toMinor(100), $this->cur, PaymentGatewayName::$STRIPE, 'k2', 'ch_2');
        $this->assertSame(1, (int) DB::table('ledger_payments')->where('idempotency_key', 'k2')->count());
    }

    public function test_failed_event_does_not_credit(): void
    {
        $this->pay->createTopUpIntent(7, Money::toMinor(30), $this->cur, PaymentGatewayName::$MTN, 'k3', 'mtn_1');
        $this->pay->handleGatewayEvent('k3', PaymentStatus::FAILED, 'mtn_1');
        $this->assertSame(PaymentStatus::FAILED, DB::table('ledger_payments')->where('idempotency_key', 'k3')->first()->status);
        $this->assertSame(0, $this->wallet->walletBalanceMinor('user', 7, $this->cur));
    }

    public function test_refund_is_idempotent(): void
    {
        $this->pay->createTopUpIntent(7, Money::toMinor(50), $this->cur, PaymentGatewayName::$STRIPE, 'k4', 'ch_4');
        $this->pay->handleGatewayEvent('k4', PaymentStatus::SUCCEEDED, 'ch_4');
        $this->wallet->holdRide(3001, 7, Money::toMinor(50), $this->cur, 'hold');

        $this->pay->refundBookingToWallet(3001, 7, Money::toMinor(50), $this->cur, PaymentGatewayName::$STRIPE, 'rf1', true);
        $this->pay->refundBookingToWallet(3001, 7, Money::toMinor(50), $this->cur, PaymentGatewayName::$STRIPE, 'rf1', true);

        $this->assertSame(0, (new LedgerService())->ownerBalanceMinor('booking', 3001, 'escrow', $this->cur));
        $this->assertSame(1, (int) DB::table('ledger_transactions')->where('kind', 'refund')->count());
    }
}
