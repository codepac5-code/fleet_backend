<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerIntegrityService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Ledger\Money;
use Illuminate\Support\Facades\DB;

class LedgerIntegrityServiceTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
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

    /** A ledger built only from real balanced postings must pass every invariant. */
    public function test_healthy_ledger_passes_all_invariants(): void
    {
        $total = Money::toMinor(50);
        $this->wallet->topUp(7, $total, $this->cur, 'tu');
        $this->wallet->holdRide(2001, 7, $total, $this->cur, 'h');
        $this->wallet->releaseRide([
            'booking_id' => 2001, 'office_id' => 3, 'driver_id' => 9,
            'currency_code' => $this->cur, 'total_minor' => $total,
            'fleet_rate' => 12, 'office_rate' => 18,
        ]);

        $report = $this->verifier->verify();

        $this->assertTrue($report['ok'], 'healthy ledger should verify: ' . json_encode($report['violations']));
        $this->assertSame([], $report['violations']);
        $this->assertGreaterThan(0, $report['transactions']);
    }

    /** A. A transaction whose entries do not net to zero is flagged. */
    public function test_detects_unbalanced_transaction(): void
    {
        $this->wallet->topUp(7, Money::toMinor(10), $this->cur, 'tu');

        // Inject a lone debit with no matching credit → transaction no longer nets to zero.
        $txId = (int) DB::table('ledger_transactions')->value('id');
        $acctId = (int) DB::table('ledger_accounts')->value('id');
        DB::table('ledger_entries')->insert([
            'transaction_id' => $txId, 'account_id' => $acctId,
            'direction' => 'debit', 'amount_minor' => 500, 'currency_code' => $this->cur,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $report = $this->verifier->verify();

        $this->assertFalse($report['ok']);
        $this->assertContains('transaction_balanced', array_column($report['violations'], 'check'));
    }

    /** B. A stored balance that drifts from the sum of its entries is flagged. */
    public function test_detects_account_out_of_sync_with_entries(): void
    {
        $this->wallet->topUp(7, Money::toMinor(10), $this->cur, 'tu');

        $wallet = DB::table('ledger_accounts')->where('account_type', 'wallet')->first();
        DB::table('ledger_accounts')->where('id', $wallet->id)
            ->update(['balance_minor' => $wallet->balance_minor - 999]);

        $report = $this->verifier->verify();

        $this->assertFalse($report['ok']);
        $checks = array_column($report['violations'], 'check');
        $this->assertContains('account_balance_matches_entries', $checks);
    }

    /** C. If the ledger stops conserving money per currency, it is flagged. */
    public function test_detects_currency_not_zero_sum(): void
    {
        $this->wallet->topUp(7, Money::toMinor(10), $this->cur, 'tu');

        // A stray standalone account with a balance and no offsetting entries anywhere.
        DB::table('ledger_accounts')->insert([
            'owner_type' => 'office', 'owner_id' => 3, 'account_type' => 'revenue',
            'currency_code' => $this->cur, 'balance_minor' => -4200,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $report = $this->verifier->verify();

        $this->assertFalse($report['ok']);
        $this->assertContains('currency_zero_sum', array_column($report['violations'], 'check'));
    }

    /** D. A wallet holding a negative real balance is flagged. */
    public function test_detects_negative_protected_balance(): void
    {
        $this->wallet->topUp(7, Money::toMinor(10), $this->cur, 'tu');

        // Force the wallet debit-positive (balance_minor > 0 == negative real balance).
        DB::table('ledger_accounts')->where('account_type', 'wallet')
            ->update(['balance_minor' => 250]);

        $report = $this->verifier->verify();

        $this->assertFalse($report['ok']);
        $this->assertContains('protected_account_non_negative', array_column($report['violations'], 'check'));
    }

    /** The floor guard blocks an overdraft at post time and rolls the posting back atomically. */
    public function test_floor_guard_blocks_wallet_overdraft(): void
    {
        $this->wallet->topUp(7, Money::toMinor(10), $this->cur, 'tu');

        try {
            // Hold more than the wallet holds — must be refused, not overdrawn.
            $this->wallet->holdRide(3001, 7, Money::toMinor(25), $this->cur, 'over-hold');
            $this->fail('overdraft should have been rejected');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('floor breach', $e->getMessage());
        }

        // Nothing moved: wallet intact, no escrow created, and the ledger still verifies.
        $this->assertSame(1000, $this->wallet->walletBalanceMinor('user', 7, $this->cur));
        $this->assertSame(0, $this->wallet->escrowBalanceMinor(3001, $this->cur));
        $this->assertTrue($this->verifier->verify()['ok']);
    }

    /** E. A non-positive entry amount is flagged. */
    public function test_detects_malformed_entry(): void
    {
        $this->wallet->topUp(7, Money::toMinor(10), $this->cur, 'tu');

        $entry = DB::table('ledger_entries')->first();
        DB::table('ledger_entries')->where('id', $entry->id)->update(['amount_minor' => 0]);

        $report = $this->verifier->verify();

        $this->assertFalse($report['ok']);
        $this->assertContains('entry_well_formed', array_column($report['violations'], 'check'));
    }
}
