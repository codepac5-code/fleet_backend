<?php

namespace App\Http\Core\Repositories\Ledger;

use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Ledger\LedgerKind;
use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\LedgerPayment;
use Illuminate\Support\Collection;

class EloquentLedgerStatementRepository implements LedgerStatementRepository
{
    public function walletEntries(string $ownerType, int $ownerId, string $currency, ?int $cursorId, int $limit): Collection
    {
        $account = LedgerAccount::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('account_type', AccountType::WALLET)
            ->where('currency_code', $currency)
            ->first();

        if ($account === null) {
            return new Collection();
        }

        $query = LedgerEntry::query()->with('transaction')->where('account_id', $account->id);

        if ($cursorId !== null) {
            $query->where('id', '<', $cursorId);
        }

        return $query->orderByDesc('id')->limit($limit + 1)->get();
    }

    public function refundsForOwner(string $ownerType, int $ownerId, ?int $cursorId, int $limit): Collection
    {
        $query = LedgerPayment::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->where('kind', LedgerKind::REFUND);

        if ($cursorId !== null) {
            $query->where('id', '<', $cursorId);
        }

        return $query->orderByDesc('id')->limit($limit + 1)->get();
    }
}
