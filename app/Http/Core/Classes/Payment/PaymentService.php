<?php

namespace App\Http\Core\Classes\Payment;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Payment\PaymentKind;
use App\Http\Core\Const\Payment\PaymentStatus;
use App\Models\LedgerPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private FleetWalletService $wallet,
        private ?EventBus $events = null
    ) {
    }

    private function emitCredited(LedgerPayment $payment): void
    {
        if ($this->events === null || $payment->owner_type !== OwnerType::USER) {
            return;
        }

        $userId = (int) $payment->owner_id;
        $balanceAfter = $this->wallet->walletBalanceMinor(OwnerType::USER, $userId, (string) $payment->currency_code);

        $this->events->emit(new DomainEvent(
            EventType::WALLET_CREDITED,
            [Channel::user($userId)],
            [
                'amount' => (int) $payment->amount_minor,
                'currency' => (string) $payment->currency_code,
                'balance_after' => $balanceAfter,
                'reason' => (string) $payment->kind,
                'ref_id' => (string) $payment->uuid,
            ]
        ));

        $this->events->emit(new DomainEvent(
            EventType::PAYMENT_SUCCEEDED,
            [Channel::user($userId)],
            [
                'payment_id' => (string) $payment->uuid,
                'booking_id' => $payment->booking_id !== null ? (int) $payment->booking_id : null,
                'amount' => (int) $payment->amount_minor,
                'currency' => (string) $payment->currency_code,
                'method' => (string) $payment->provider,
            ]
        ));
    }

    public function createTopUpIntent(int $userId, int $amountMinor, string $currency, string $provider, string $idempotencyKey, ?string $providerRef = null, array $meta = []): LedgerPayment
    {
        $this->assertPositive($amountMinor);

        return $this->firstOrCreateIntent([
            'idempotency_key' => $idempotencyKey,
            'provider' => $provider,
            'provider_ref' => $providerRef,
            'kind' => PaymentKind::TOPUP,
            'owner_type' => OwnerType::USER,
            'owner_id' => $userId,
            'booking_id' => null,
            'amount_minor' => $amountMinor,
            'currency_code' => $currency,
            'meta' => $meta,
        ]);
    }

    public function handleGatewayEvent(string $idempotencyKey, string $eventStatus, ?string $providerRef = null): LedgerPayment
    {
        return $this->onConnection(function () use ($idempotencyKey, $eventStatus, $providerRef) {
            $payment = LedgerPayment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                throw new RuntimeException('unknown payment for key: ' . $idempotencyKey);
            }

            if ($providerRef && !$payment->provider_ref) {
                $payment->provider_ref = $providerRef;
                $payment->save();
            }

            if ($payment->status === PaymentStatus::SUCCEEDED || $payment->status === PaymentStatus::REFUNDED) {
                return $payment;
            }

            if ($eventStatus === PaymentStatus::FAILED) {
                if ($payment->status === PaymentStatus::PENDING) {
                    $payment->status = PaymentStatus::FAILED;
                    $payment->save();
                }

                return $payment;
            }

            if ($eventStatus === PaymentStatus::SUCCEEDED && $payment->status === PaymentStatus::PENDING) {
                // The wallet is credited in the account's LOCAL currency. For a
                // cross-currency top-up (charged in USD, wallet in SYP) the
                // converted local amount was computed at request time and stored
                // in meta; the charge amount/currency here are what Stripe
                // actually settled. Same-currency top-ups have no meta and fall
                // back to the charge values, preserving the old behaviour.
                $meta = $payment->meta ?? [];
                $creditCurrency = $meta['wallet_currency_code'] ?? $payment->currency_code;
                $creditAmountMinor = isset($meta['wallet_amount_minor'])
                    ? (int) $meta['wallet_amount_minor']
                    : (int) $payment->amount_minor;

                $transaction = $this->wallet->topUp(
                    (int) $payment->owner_id,
                    $creditAmountMinor,
                    $creditCurrency,
                    'pay:' . $payment->uuid,
                    'ledger_payment',
                    $payment->id
                );

                $payment->status = PaymentStatus::SUCCEEDED;
                $payment->ledger_transaction_uuid = $transaction->uuid;
                $payment->save();

                $this->emitCredited($payment);
            }

            return $payment;
        });
    }

    public function refundBookingToWallet(int $bookingId, int $userId, int $amountMinor, string $currency, string $provider, string $idempotencyKey, bool $fromEscrow = true): LedgerPayment
    {
        $this->assertPositive($amountMinor);

        return $this->onConnection(function () use ($bookingId, $userId, $amountMinor, $currency, $provider, $idempotencyKey, $fromEscrow) {
            $existing = LedgerPayment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->status === PaymentStatus::REFUNDED) {
                return $existing;
            }

            $payment = $existing ?: LedgerPayment::query()->create([
                'uuid' => (string) Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'provider' => $provider,
                'provider_ref' => null,
                'kind' => PaymentKind::REFUND,
                'owner_type' => OwnerType::USER,
                'owner_id' => $userId,
                'booking_id' => $bookingId,
                'amount_minor' => $amountMinor,
                'currency_code' => $currency,
                'status' => PaymentStatus::PENDING,
                'meta' => ['from_escrow' => $fromEscrow],
            ]);

            $transaction = $fromEscrow
                ? $this->wallet->refundFromEscrow($bookingId, $userId, $amountMinor, $currency, 'refund:' . $payment->uuid)
                : $this->wallet->refundFromFleet($bookingId, $userId, $amountMinor, $currency, 'refund:' . $payment->uuid);

            $payment->status = PaymentStatus::REFUNDED;
            $payment->ledger_transaction_uuid = $transaction->uuid;
            $payment->save();

            $this->emitCredited($payment);

            return $payment;
        });
    }

    private function firstOrCreateIntent(array $attributes): LedgerPayment
    {
        return $this->onConnection(function () use ($attributes) {
            $existing = LedgerPayment::query()
                ->where('idempotency_key', $attributes['idempotency_key'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            return LedgerPayment::query()->create(array_merge($attributes, [
                'uuid' => (string) Str::uuid(),
                'status' => PaymentStatus::PENDING,
            ]));
        });
    }

    private function onConnection(callable $callback)
    {
        $connection = (new LedgerPayment)->getConnectionName();

        return DB::connection($connection)->transaction($callback);
    }

    private function assertPositive(int $amountMinor): void
    {
        if ($amountMinor <= 0) {
            throw new RuntimeException('amount must be positive');
        }
    }
}
