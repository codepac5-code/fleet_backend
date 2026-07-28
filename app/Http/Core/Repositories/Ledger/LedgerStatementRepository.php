<?php

namespace App\Http\Core\Repositories\Ledger;

use Illuminate\Support\Collection;

interface LedgerStatementRepository
{
    public function walletEntries(string $ownerType, int $ownerId, string $currency, ?int $cursorId, int $limit): Collection;

    public function refundsForOwner(string $ownerType, int $ownerId, ?int $cursorId, int $limit): Collection;
}
