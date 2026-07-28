<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\Direction;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LedgerService
{
    public function connectionName(): ?string
    {
        return (new LedgerTransaction)->getConnectionName();
    }

    public function ensureAccount(string $ownerType, $ownerId, string $accountType, string $currency, ?string $code = null): LedgerAccount
    {
        if (!in_array($accountType, AccountType::all(), true)) {
            throw new RuntimeException('invalid account type: ' . $accountType);
        }

        return LedgerAccount::query()->firstOrCreate(
            [
                'owner_type' => $ownerType,
                'owner_id' => (int) $ownerId,
                'account_type' => $accountType,
                'currency_code' => $currency,
            ],
            [
                'balance_minor' => 0,
                'code' => $code,
            ]
        );
    }

    public function post(array $params): LedgerTransaction
    {
        $idempotencyKey = $params['idempotency_key'] ?? null;
        $kind = $params['kind'];
        $currency = $params['currency_code'];
        $lines = array_values(array_filter(
            $params['entries'] ?? [],
            fn ($line) => (int) ($line['amount_minor'] ?? 0) !== 0
        ));
        $referenceType = $params['reference_type'] ?? null;
        $referenceId = $params['reference_id'] ?? null;
        $description = $params['description'] ?? null;
        $allowOverdraft = (bool) ($params['allow_overdraft'] ?? false);

        if (count($lines) < 2) {
            throw new RuntimeException('a ledger transaction needs at least two entries');
        }

        $this->assertBalanced($lines);

        $connection = $this->connectionName();

        return DB::connection($connection)->transaction(function () use (
            $idempotencyKey, $kind, $currency, $lines, $referenceType, $referenceId, $description, $allowOverdraft
        ) {
            if ($idempotencyKey) {
                $existing = LedgerTransaction::query()
                    ->where('idempotency_key', $idempotencyKey)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $transaction = LedgerTransaction::query()->create([
                'uuid' => (string) Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'kind' => $kind,
                'currency_code' => $currency,
                'status' => 'posted',
                'description' => $description,
                'posted_at' => now(),
            ]);

            foreach ($lines as $line) {
                $account = $this->lockAccount(
                    $line['owner_type'],
                    $line['owner_id'],
                    $line['account_type'],
                    $currency,
                    $line['code'] ?? null
                );

                $amount = (int) $line['amount_minor'];
                $direction = $line['direction'];
                $signed = $direction === Direction::DEBIT ? $amount : -$amount;

                $account->balance_minor += $signed;

                $this->guardFloor($account, $allowOverdraft);

                $account->save();

                LedgerEntry::query()->create([
                    'transaction_id' => $transaction->id,
                    'account_id' => $account->id,
                    'direction' => $direction,
                    'amount_minor' => $amount,
                    'currency_code' => $currency,
                    'balance_after_minor' => $account->balance_minor,
                ]);
            }

            return $transaction;
        });
    }

    public function ownerBalanceMinor(string $ownerType, $ownerId, string $accountType, string $currency): int
    {
        $account = LedgerAccount::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', (int) $ownerId)
            ->where('account_type', $accountType)
            ->where('currency_code', $currency)
            ->first();

        if (!$account) {
            return 0;
        }

        $signed = (int) $account->balance_minor;

        return AccountType::isCreditNormal($accountType) ? -$signed : $signed;
    }

    public function lockOwnerBalanceMinor(string $ownerType, $ownerId, string $accountType, string $currency): int
    {
        $account = LedgerAccount::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', (int) $ownerId)
            ->where('account_type', $accountType)
            ->where('currency_code', $currency)
            ->lockForUpdate()
            ->first();

        if (!$account) {
            return 0;
        }

        $signed = (int) $account->balance_minor;

        return AccountType::isCreditNormal($accountType) ? -$signed : $signed;
    }

    private function lockAccount(string $ownerType, $ownerId, string $accountType, string $currency, ?string $code): LedgerAccount
    {
        $this->ensureAccount($ownerType, $ownerId, $accountType, $currency, $code);

        return LedgerAccount::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', (int) $ownerId)
            ->where('account_type', $accountType)
            ->where('currency_code', $currency)
            ->lockForUpdate()
            ->first();
    }

    /** Account types that must never hold a negative real balance — money you don't have can't move. */
    private const PROTECTED = [AccountType::WALLET, AccountType::ESCROW];

    /**
     * Floor guard: refuse to drive a wallet or escrow account below zero. Both
     * are credit-normal, so a negative real balance surfaces as a positive
     * `balance_minor`. Throwing here rolls back the whole posting (we're inside
     * the DB transaction), so an underfunded hold/spend fails atomically instead
     * of silently leaving the ledger owing money. Callers that legitimately need
     * to overshoot (e.g. an authorized adjustment) pass `allow_overdraft`.
     */
    private function guardFloor(LedgerAccount $account, bool $allowOverdraft): void
    {
        if ($allowOverdraft || !in_array($account->account_type, self::PROTECTED, true)) {
            return;
        }

        if ((int) $account->balance_minor > 0) {
            throw new RuntimeException(sprintf(
                'ledger floor breach: %s:%s %s:%s would go negative by %d',
                $account->owner_type,
                $account->owner_id,
                $account->account_type,
                $account->currency_code,
                (int) $account->balance_minor,
            ));
        }
    }

    private function assertBalanced(array $lines): void
    {
        $debit = 0;
        $credit = 0;

        foreach ($lines as $line) {
            $amount = (int) $line['amount_minor'];

            if ($amount <= 0) {
                throw new RuntimeException('ledger entry amount must be positive');
            }

            if ($line['direction'] === Direction::DEBIT) {
                $debit += $amount;
            } elseif ($line['direction'] === Direction::CREDIT) {
                $credit += $amount;
            } else {
                throw new RuntimeException('invalid direction: ' . $line['direction']);
            }
        }

        if ($debit !== $credit) {
            throw new RuntimeException('unbalanced ledger transaction: debit ' . $debit . ' != credit ' . $credit);
        }
    }
}
