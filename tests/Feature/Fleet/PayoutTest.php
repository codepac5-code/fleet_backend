<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Payment\PayoutService;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Payment\PayoutStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Models\EventOutbox;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayoutTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000016_create_payout_requests_table.php',
        '2026_06_25_000007_create_event_outbox_table.php',
    ];

    private FleetWalletService $wallet;
    private PayoutService $payouts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallet = new FleetWalletService(new LedgerService());
        $this->payouts = new PayoutService($this->wallet, new EventBus());
    }

    public function test_pay_emits_wallet_payout_event(): void
    {
        $this->fundDriverWallet();
        $request = $this->payouts->request('driver', 9, AccountType::WALLET, 5000, 'USD');
        $this->payouts->pay($request->id);

        $event = EventOutbox::query()->where('type', EventType::WALLET_PAYOUT)->first();

        $this->assertNotNull($event);
        $this->assertContains('driver.9', $event->channels);
    }

    private function fundDriverWallet(): void
    {
        $this->wallet->topUp(7, 10000, 'USD', 'fund:5001');
        $this->wallet->holdRide(5001, 7, 10000, 'USD', 'hold:5001');
        $this->wallet->releaseRide([
            'booking_id' => 5001,
            'office_id' => 3,
            'driver_id' => 9,
            'currency_code' => 'USD',
            'total_minor' => 10000,
            'fleet_rate' => 18.0,
            'office_rate' => 0.0,
        ]);
    }

    /**
     * A payout larger than the balance is refused.
     *
     * Assert the machine-readable `errorCode`, not the message: DomainException
     * humanises the code into its message ("insufficient balance."), so
     * expectExceptionMessage('insufficient_balance') can never match. The code
     * is the contract the API surfaces as `error.code` anyway.
     */
    public function test_request_rejects_amount_over_balance(): void
    {
        $this->fundDriverWallet();
        $before = $this->wallet->walletBalanceMinor('driver', 9, 'USD');

        try {
            $this->payouts->request('driver', 9, AccountType::WALLET, 99999, 'USD');
            $this->fail('an over-balance payout should have been rejected');
        } catch (DomainException $e) {
            $this->assertSame('insufficient_balance', $e->errorCode);
        }

        // …and the refusal left the wallet untouched.
        $this->assertSame($before, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));
    }

    public function test_pay_debits_wallet_and_is_idempotent(): void
    {
        $this->fundDriverWallet();
        $this->assertSame(8200, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));

        $request = $this->payouts->request('driver', 9, AccountType::WALLET, 5000, 'USD');
        $this->assertSame(PayoutStatus::PENDING, $request->status);

        $this->payouts->pay($request->id);
        $this->payouts->pay($request->id);

        $this->assertSame(3200, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));
        $this->assertSame(PayoutStatus::PAID, $request->fresh()->status);
        $this->assertSame(1, (int) DB::table('ledger_transactions')->where('kind', 'payout')->count());
    }

    public function test_reject_leaves_balance_untouched(): void
    {
        $this->fundDriverWallet();

        $request = $this->payouts->request('driver', 9, AccountType::WALLET, 5000, 'USD');
        $this->payouts->reject($request->id, 'invalid bank details');

        $this->assertSame(PayoutStatus::REJECTED, $request->fresh()->status);
        $this->assertSame(8200, $this->wallet->walletBalanceMinor('driver', 9, 'USD'));
    }
}
