<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Const\Ledger\AccountType;
use Illuminate\Support\Facades\DB;

/**
 * Ledger governance: a READ-ONLY reconciliation engine that continuously proves
 * the money is consistent. It asserts the invariants a double-entry ledger must
 * always hold — nothing here writes, so it is safe to run on live data at any
 * cadence (see the fleet:ledger-verify command).
 *
 * Convention: `ledger_accounts.balance_minor` is stored DEBIT-POSITIVE. A
 * credit-normal account's real balance is `-balance_minor`; a debit-normal
 * account's is `+balance_minor`. Each entry's signed value is `+amount` for a
 * debit and `-amount` for a credit.
 *
 * Invariants checked:
 *   A. Every transaction is balanced (its entries net to zero).
 *   B. Each account's stored balance equals the sum of its entries.
 *   C. The whole ledger nets to zero per currency (money is conserved).
 *   D. Protected accounts (wallet, escrow) never hold a negative real balance.
 *   E. Every entry is a positive amount with a valid direction.
 */
class LedgerIntegrityService
{
    /** Cap on violations returned per check so a broken ledger can't OOM a report. */
    private const SAMPLE = 100;

    public function __construct(private ?string $connection = null)
    {
    }

    private function db()
    {
        return DB::connection($this->connection);
    }

    /**
     * Run every invariant. Returns a structured report; `ok` is false if ANY
     * invariant is violated.
     *
     * @return array{ok:bool, accounts:int, transactions:int, entries:int, violations:array<int, array<string,mixed>>}
     */
    public function verify(): array
    {
        $violations = array_merge(
            $this->unbalancedTransactions(),
            $this->accountsOutOfSyncWithEntries(),
            $this->currenciesNotZeroSum(),
            $this->negativeProtectedBalances(),
            $this->malformedEntries(),
        );

        return [
            'ok' => $violations === [],
            'accounts' => (int) $this->db()->table('ledger_accounts')->count(),
            'transactions' => (int) $this->db()->table('ledger_transactions')->count(),
            'entries' => (int) $this->db()->table('ledger_entries')->count(),
            'violations' => $violations,
        ];
    }

    private const NET = "SUM(CASE direction WHEN 'debit' THEN amount_minor ELSE -amount_minor END)";

    /** A. Every transaction's entries must net to zero. */
    private function unbalancedTransactions(): array
    {
        $rows = $this->db()->table('ledger_entries')
            ->select('transaction_id', DB::raw(self::NET . ' AS net'))
            ->groupBy('transaction_id')
            ->havingRaw(self::NET . ' <> 0')
            ->limit(self::SAMPLE)
            ->get();

        return $rows->map(fn ($r) => [
            'check' => 'transaction_balanced',
            'transaction_id' => (int) $r->transaction_id,
            'net_minor' => (int) $r->net,
        ])->all();
    }

    /** B. Each account's stored balance must equal the sum of its entries. */
    private function accountsOutOfSyncWithEntries(): array
    {
        $sums = $this->db()->table('ledger_entries')
            ->select('account_id', DB::raw(self::NET . ' AS net'))
            ->groupBy('account_id')
            ->pluck('net', 'account_id');

        $out = [];
        $this->db()->table('ledger_accounts')
            ->select('id', 'owner_type', 'owner_id', 'account_type', 'currency_code', 'balance_minor')
            ->orderBy('id')
            ->each(function ($a) use ($sums, &$out) {
                $expected = (int) ($sums[$a->id] ?? 0);
                if ((int) $a->balance_minor !== $expected && count($out) < self::SAMPLE) {
                    $out[] = [
                        'check' => 'account_balance_matches_entries',
                        'account_id' => (int) $a->id,
                        'account' => "{$a->owner_type}:{$a->owner_id}:{$a->account_type}:{$a->currency_code}",
                        'stored_minor' => (int) $a->balance_minor,
                        'entries_minor' => $expected,
                    ];
                }
            });

        return $out;
    }

    /** C. The whole ledger must conserve money — net zero per currency. */
    private function currenciesNotZeroSum(): array
    {
        $rows = $this->db()->table('ledger_accounts')
            ->select('currency_code', DB::raw('SUM(balance_minor) AS total'))
            ->groupBy('currency_code')
            ->havingRaw('SUM(balance_minor) <> 0')
            ->get();

        return $rows->map(fn ($r) => [
            'check' => 'currency_zero_sum',
            'currency_code' => (string) $r->currency_code,
            'net_minor' => (int) $r->total,
        ])->all();
    }

    /**
     * D. A wallet or escrow account must never hold a negative real balance —
     * you cannot spend or hold money you do not have. Both are credit-normal, so
     * a negative real balance means `balance_minor > 0`.
     */
    private function negativeProtectedBalances(): array
    {
        $rows = $this->db()->table('ledger_accounts')
            ->whereIn('account_type', [AccountType::WALLET, AccountType::ESCROW])
            ->where('balance_minor', '>', 0)
            ->limit(self::SAMPLE)
            ->get();

        return $rows->map(fn ($a) => [
            'check' => 'protected_account_non_negative',
            'account_id' => (int) $a->id,
            'account' => "{$a->owner_type}:{$a->owner_id}:{$a->account_type}:{$a->currency_code}",
            'real_balance_minor' => -1 * (int) $a->balance_minor,
        ])->all();
    }

    /** E. Every entry must be a positive amount with a valid direction. */
    private function malformedEntries(): array
    {
        $rows = $this->db()->table('ledger_entries')
            ->where('amount_minor', '<=', 0)
            ->orWhereNotIn('direction', ['debit', 'credit'])
            ->limit(self::SAMPLE)
            ->get();

        return $rows->map(fn ($e) => [
            'check' => 'entry_well_formed',
            'entry_id' => (int) $e->id,
            'transaction_id' => (int) $e->transaction_id,
            'amount_minor' => (int) $e->amount_minor,
            'direction' => (string) $e->direction,
        ])->all();
    }
}
