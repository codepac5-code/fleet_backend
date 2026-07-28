<?php

namespace App\Http\Services\User\Payments\Logic;

use App\Http\Core\Classes\Account\CardGateway;
use App\Http\Core\Classes\Ledger\CurrencyConverter;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\WalletStatementService;
use App\Http\Core\Classes\Payment\PaymentService;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Core\Const\Payment\PaymentKind;
use App\Http\Core\Const\Payment\PaymentStatus;
use App\Http\Core\Const\Payment\StripeCurrencies;
use App\Http\Core\Exceptions\DomainException;
use App\Models\LedgerPayment;
use App\Http\Services\User\Support\Cursor;
use App\Http\Services\User\Support\Presenters\MoneyPresenter;

class WalletService
{
    public function __construct(
        private FleetWalletService $wallet,
        private WalletStatementService $statement,
        private PaymentService $payments,
        private CardGateway $cards,
        private CurrencyConverter $fx
    ) {
    }

    public function balance(int $userId, ?string $currencyCode): array
    {
        $currency = MoneyPresenter::currency($currencyCode);
        $minor = $this->wallet->walletBalanceMinor(OwnerType::USER, $userId, $currency['code']);

        return [
            'balance' => MoneyPresenter::decimal($minor, $currency['decimals']),
            'currency' => $currency['code'],
            'symbol' => $currency['symbol'],
            'decimals' => $currency['decimals'],
        ];
    }

    public function transactions(int $userId, ?string $currencyCode, ?string $cursor, $limit): array
    {
        $currency = MoneyPresenter::currency($currencyCode);
        $limit = Cursor::limit($limit);

        $result = $this->statement->transactions(OwnerType::USER, $userId, $currency['code'], Cursor::decode($cursor), $limit);

        $items = array_map(function (array $e) use ($currency) {
            $signedMinor = ($e['direction'] === 'credit' ? 1 : -1) * (int) $e['amount_minor'];
            $afterMinor = -1 * (int) $e['balance_after_minor'];
            $beforeMinor = $afterMinor - $signedMinor;

            return [
                'id' => (int) $e['id'],
                'amount' => MoneyPresenter::decimal($signedMinor, $currency['decimals']),
                'balance_before' => MoneyPresenter::decimal($beforeMinor, $currency['decimals']),
                'balance_after' => MoneyPresenter::decimal($afterMinor, $currency['decimals']),
                'status' => 'active',
                'transaction_type' => $e['kind'],
                'transaction_reference' => null,
                'description' => $e['description'],
                'paymentName' => null,
                'created_at' => $e['at'],
            ];
        }, $result['data']);

        $next = $result['meta']['next_cursor'] ?? null;

        return [
            'items' => $items,
            'nextCursor' => $next !== null ? Cursor::encode((int) $next) : null,
        ];
    }

    /**
     * What currencies the rider may top up in. When their wallet's local
     * currency is one Stripe can settle, `directCharge` is true and the app can
     * charge it straight. Otherwise the app must present `chargeCurrencies` (the
     * supported currencies we hold a usable FX rate for) so the rider pays in
     * one of those and the wallet is credited the converted local amount.
     */
    public function topUpOptions(): array
    {
        $wallet = MoneyPresenter::currency(null);
        $walletCode = strtoupper($wallet['code']);

        $charge = [];
        foreach (\App\Models\Currency::query()->where('is_active', true)->get() as $c) {
            $code = strtoupper($c->code);

            if (! StripeCurrencies::isSupported($code)) {
                continue;
            }

            // Cross-currency options must be convertible to the wallet currency.
            if ($code !== $walletCode && ! $this->fx->canConvert($code, $walletCode)) {
                continue;
            }

            $charge[] = [
                'code' => $code,
                'symbol' => $c->symbol ?? $code,
                'decimals' => (int) $c->decimals,
            ];
        }

        return [
            'walletCurrency' => $wallet['code'],
            'walletSymbol' => $wallet['symbol'],
            'walletDecimals' => (int) $wallet['decimals'],
            'directCharge' => StripeCurrencies::isSupported($walletCode),
            'chargeCurrencies' => $charge,
        ];
    }

    /**
     * Preview a top-up: given a charge currency + amount, return the local
     * wallet amount the rider will receive, so the picker can show
     * "you'll receive ≈ X" before they pay.
     */
    public function topUpQuote(?string $chargeCurrencyCode, int $amountMinor): array
    {
        $wallet = MoneyPresenter::currency(null);
        $charge = ($chargeCurrencyCode !== null && $chargeCurrencyCode !== '')
            ? MoneyPresenter::currency($chargeCurrencyCode)
            : $wallet;

        $walletAmountMinor = $this->fx->convertMinor($amountMinor, $charge['code'], $wallet['code']);

        return [
            'chargeCurrency' => $charge['code'],
            'chargeSymbol' => $charge['symbol'],
            'chargeAmountMinor' => $amountMinor,
            'chargeAmount' => MoneyPresenter::decimal($amountMinor, (int) $charge['decimals']),
            'walletCurrency' => $wallet['code'],
            'walletSymbol' => $wallet['symbol'],
            'walletAmountMinor' => $walletAmountMinor,
            'walletAmount' => MoneyPresenter::decimal($walletAmountMinor, (int) $wallet['decimals']),
        ];
    }

    /**
     * Top up the rider's wallet.
     *
     * `$chargeCurrencyCode` is the currency the rider PAYS in — normally their
     * local currency, but when the local currency is one Stripe can't settle
     * (e.g. SYP in Syria) the app sends a supported currency (USD) instead. The
     * wallet is always credited in the account's LOCAL currency: for a
     * cross-currency top-up we convert the charge to a local credit at the
     * admin-maintained rate and stash the result in the payment meta, because
     * the webhook that finalises the credit runs with no request context (no
     * X-Country) and so cannot re-derive the wallet currency or FX rate itself.
     */
    public function topUp(int $userId, ?string $chargeCurrencyCode, int $amountMinor, ?int $paymentMethodId, string $idempotencyKey): array
    {
        $walletCurrency = MoneyPresenter::currency(null);
        $chargeCurrency = ($chargeCurrencyCode !== null && $chargeCurrencyCode !== '')
            ? MoneyPresenter::currency($chargeCurrencyCode)
            : $walletCurrency;

        if (! StripeCurrencies::isSupported($chargeCurrency['code'])) {
            throw DomainException::make('charge_currency_unsupported', 422, 'This currency cannot be charged. Please choose a supported currency.');
        }

        // Same currency → identity (no rate needed). Cross-currency → convert now
        // at the request-time rate; CurrencyConverter throws `fx_rate_unset` if
        // the admin hasn't set the local rate, which is the correct fail-loud.
        $walletAmountMinor = $this->fx->convertMinor($amountMinor, $chargeCurrency['code'], $walletCurrency['code']);

        $key = $idempotencyKey !== '' ? $idempotencyKey : ('topup:' . $userId . ':' . $amountMinor . ':' . $chargeCurrency['code']);

        // Real Stripe PaymentIntent — the app confirms it with the returned
        // clientSecret; the wallet is credited later by the gateway webhook
        // (PaymentService::handleGatewayEvent on payment_intent.succeeded).
        $pi = $this->cards->paymentIntent($userId, $amountMinor, $chargeCurrency['code'], $paymentMethodId, $key);

        $meta = array_filter([
            'payment_method_id' => $paymentMethodId,
            'wallet_currency_code' => $walletCurrency['code'],
            'wallet_amount_minor' => $walletAmountMinor,
            'charge_currency_code' => $chargeCurrency['code'],
            'charge_amount_minor' => $amountMinor,
        ], fn ($v) => $v !== null);

        $intent = $this->payments->createTopUpIntent(
            $userId,
            $amountMinor,
            $chargeCurrency['code'],
            'stripe',
            $key,
            $pi['id'],
            $meta
        );

        return [
            'ledgerId' => (int) $intent->id,
            'paymentIntentId' => $pi['id'],
            'status' => $pi['status'],
            'clientSecret' => $pi['clientSecret'],
            'requiresAction' => $pi['requiresAction'],
            'chargeCurrency' => $chargeCurrency['code'],
            'chargeAmountMinor' => $amountMinor,
            'walletCurrency' => $walletCurrency['code'],
            'walletAmountMinor' => $walletAmountMinor,
            // Handed to the client so the SDK is keyed from the SAME Stripe
            // account that minted this secret — the identical reasoning as
            // StripeCardGateway::setupIntent. Without it the app cannot confirm
            // the intent at all, and the top-up silently never charged.
            'publishableKey' => (string) config('services.stripe.public'),
        ];
    }

    /**
     * Confirm a top-up on demand by asking the gateway for the PaymentIntent's
     * current status, and settle it (credit the wallet) if it has succeeded.
     *
     * The wallet is normally credited by the async `payment_intent.succeeded`
     * webhook, but that can't reach a local dev server and can lag in
     * production — so after the app confirms the card it calls this to get an
     * immediate, authoritative result. Settlement runs through the SAME
     * idempotent path as the webhook (`handleGatewayEvent`), so a later webhook
     * for the same intent is a no-op.
     */
    public function verifyTopUp(int $userId, string $paymentIntentId): array
    {
        $payment = LedgerPayment::query()
            ->where('provider_ref', $paymentIntentId)
            ->where('owner_id', $userId)
            ->where('kind', PaymentKind::TOPUP)
            ->first();

        if ($payment === null) {
            throw DomainException::make('payment_not_found', 404);
        }

        if ($payment->status === PaymentStatus::PENDING) {
            $status = $this->cards->paymentIntentStatus($paymentIntentId);

            if ($status === 'succeeded') {
                $this->payments->handleGatewayEvent($payment->idempotency_key, PaymentStatus::SUCCEEDED, $paymentIntentId);
                $payment->refresh();
            }
        }

        return [
            'status' => $payment->status,
            'credited' => $payment->status === PaymentStatus::SUCCEEDED,
            'wallet' => $this->balance($userId, null),
        ];
    }
}
