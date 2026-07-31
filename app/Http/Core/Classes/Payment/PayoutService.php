<?php

namespace App\Http\Core\Classes\Payment;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Payment\PayoutStatus;
use App\Http\Core\Exceptions\DomainException;
use App\Models\PayoutRequest;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayoutService
{
    public function __construct(
        private FleetWalletService $wallet,
        private ?EventBus $events = null
    ) {
    }

    public function request(string $ownerType, int $ownerId, string $sourceAccount, int $amountMinor, string $currency): PayoutRequest
    {
        if ($amountMinor <= 0) {
            throw DomainException::make('invalid_amount', 422);
        }

        $this->assertSource($sourceAccount);

        $available = $this->availableMinor($ownerType, $ownerId, $sourceAccount, $currency);

        if ($amountMinor > $available) {
            throw DomainException::make('insufficient_balance', 422);
        }

        return PayoutRequest::query()->create([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'source_account' => $sourceAccount,
            'amount_minor' => $amountMinor,
            'currency_code' => $currency,
            'status' => PayoutStatus::PENDING,
        ]);
    }

    public function pay(int $requestId): PayoutRequest
    {
        $connection = (new PayoutRequest)->getConnectionName();

        return DB::connection($connection)->transaction(function () use ($requestId) {
            $request = PayoutRequest::query()->lockForUpdate()->findOrFail($requestId);

            if ($request->status === PayoutStatus::PAID) {
                return $request;
            }

            if ($request->status !== PayoutStatus::PENDING) {
                throw new RuntimeException('payout request is not payable');
            }

            $available = $this->availableMinor($request->owner_type, (int) $request->owner_id, $request->source_account, $request->currency_code);

            if ((int) $request->amount_minor > $available) {
                throw new RuntimeException('insufficient_balance');
            }

            $transaction = $this->wallet->payout(
                $request->owner_type,
                (int) $request->owner_id,
                $request->source_account,
                (int) $request->amount_minor,
                $request->currency_code,
                'payout:' . $request->id
            );

            $request->status = PayoutStatus::PAID;
            $request->ledger_transaction_uuid = $transaction->uuid;
            $request->processed_at = now();
            $request->save();

            if ($this->events !== null) {
                $channel = $request->owner_type === 'office'
                    ? Channel::office((int) $request->owner_id)
                    : Channel::driver((int) $request->owner_id);

                // A payout REQUEST is an admin queue item; telling only the
                // requester meant the desk that has to approve it learnt
                // nothing until someone refreshed the payouts page.
                $this->events->emit(new DomainEvent(
                    EventType::WALLET_PAYOUT,
                    [$channel, Channel::admin()],
                    [
                        'payout_id' => (int) $request->id,
                        'amount' => (int) $request->amount_minor,
                        'currency_code' => $request->currency_code,
                    ]
                ));
            }

            return $request;
        });
    }

    public function reject(int $requestId, ?string $note = null): PayoutRequest
    {
        $request = PayoutRequest::query()->findOrFail($requestId);

        if ($request->status === PayoutStatus::PENDING) {
            $request->status = PayoutStatus::REJECTED;
            $request->note = $note;
            $request->processed_at = now();
            $request->save();
        }

        return $request;
    }

    public function pending(int $limit = 100): array
    {
        return PayoutRequest::query()
            ->where('status', PayoutStatus::PENDING)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }

    public function listFor(string $ownerType, int $ownerId, int $limit = 50): array
    {
        return PayoutRequest::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->all();
    }

    private function availableMinor(string $ownerType, int $ownerId, string $sourceAccount, string $currency): int
    {
        return $sourceAccount === AccountType::WALLET
            ? $this->wallet->walletBalanceMinor($ownerType, $ownerId, $currency)
            : $this->wallet->revenueBalanceMinor($ownerType, $ownerId, $currency);
    }

    private function assertSource(string $sourceAccount): void
    {
        if (!in_array($sourceAccount, [AccountType::WALLET, AccountType::REVENUE], true)) {
            throw new RuntimeException('payout source must be wallet or revenue');
        }
    }
}
