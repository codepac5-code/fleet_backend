<?php

namespace App\Http\Core\Classes\Ledger;

use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Repositories\Ledger\LedgerStatementRepository;
use App\Models\LedgerEntry;
use App\Models\LedgerPayment;

class WalletStatementService
{
    public function __construct(private LedgerStatementRepository $repository)
    {
    }

    public function transactions(string $ownerType, int $ownerId, string $currency, ?int $cursorId, int $limit): array
    {
        $rows = $this->repository->walletEntries($ownerType, $ownerId, $currency, $cursorId, $limit);
        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        return [
            'data' => $items->map(fn (LedgerEntry $e) => [
                'id' => (int) $e->id,
                'direction' => $e->direction,
                'amount_minor' => (int) $e->amount_minor,
                'balance_after_minor' => (int) $e->balance_after_minor,
                'currency_code' => $e->currency_code,
                'kind' => optional($e->transaction)->kind,
                'description' => optional($e->transaction)->description,
                'at' => optional(optional($e->transaction)->posted_at ?? $e->created_at)->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'next_cursor' => $hasMore ? (string) $items->last()->id : null,
                'has_more' => $hasMore,
            ],
        ];
    }

    public function refunds(int $userId, ?int $cursorId, int $limit): array
    {
        $rows = $this->repository->refundsForOwner(OwnerType::USER, $userId, $cursorId, $limit);
        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        return [
            'data' => $items->map(fn (LedgerPayment $p) => [
                'uuid' => $p->uuid,
                'booking_id' => $p->booking_id !== null ? (int) $p->booking_id : null,
                'amount_minor' => (int) $p->amount_minor,
                'currency_code' => $p->currency_code,
                'status' => $p->status,
                'at' => optional($p->created_at)->toIso8601String(),
            ])->values()->all(),
            'meta' => [
                'next_cursor' => $hasMore ? (string) $items->last()->id : null,
                'has_more' => $hasMore,
            ],
        ];
    }
}
