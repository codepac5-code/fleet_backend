<?php

namespace App\Http\Services\Driver\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Ledger\DriverCurrency;
use App\Http\Core\Classes\Ledger\DriverDuesService;
use App\Http\Core\Classes\Ledger\DriverEarningsService;
use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\WalletStatementService;
use App\Http\Core\Classes\Payment\PayoutService;
use App\Http\Core\Const\Ledger\OwnerType;
use App\Http\Services\User\Support\Cursor;
use App\Http\Services\User\Support\Presenters\MoneyPresenter;
use App\Models\DriverAppSetting;
use Carbon\Carbon;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Driver wallet, transactions, earnings dashboard, and payout requests. Wires
 * the shared ledger services scoped to `OwnerType::DRIVER`.
 */
class DriverWalletController extends Controller
{
    public function __construct(
        private FleetWalletService $wallet,
        private WalletStatementService $statement,
        private DriverEarningsService $earnings,
        private PayoutService $payouts,
        private DriverDuesService $dues,
    ) {
    }

    public function earnings(Request $request): JsonResponse
    {
        $driverId = (int) $request->user()->id;
        $currency = DriverCurrency::resolve($request->user(), $request->header('X-Country'));
        $range = (string) $request->query('range', 'today');

        return Reply::ok($this->earnings->summary($driverId, $range, $currency));
    }

    public function wallet(Request $request): JsonResponse
    {
        $driverId = (int) $request->user()->id;
        $currencyMeta = MoneyPresenter::currency(DriverCurrency::resolve($request->user(), $request->header('X-Country')));
        $currency = $currencyMeta['code'];
        $balanceMinor = $this->wallet->walletBalanceMinor(OwnerType::DRIVER, $driverId, $currency);

        $pending = 0;
        foreach ($this->payouts->listFor(OwnerType::DRIVER, $driverId, 50) as $p) {
            if (($p['status'] ?? null) === 'pending') {
                $pending += (int) ($p['amount_minor'] ?? 0);
            }
        }

        // Payout account + schedule from the driver's settings (was stubbed null).
        $settings = DriverAppSetting::query()->where('driver_id', $driverId)->first();
        $bankId = $settings?->payout_bank_id;
        $bankMask = ($bankId !== null && $bankId !== '')
            ? '•••• ' . substr((string) $bankId, -4)
            : null;
        // Auto weekly payout runs at the start of each week — surface the next one.
        $nextPayout = ($settings && $settings->auto_payout)
            ? now()->next(Carbon::MONDAY)->format('D j M')
            : null;

        return Reply::ok([
            'balance' => [
                'balance_minor' => $balanceMinor,
                'currency_code' => $currency,
                'balance' => MoneyPresenter::decimal($balanceMinor, $currencyMeta['decimals']),
                'symbol' => $currencyMeta['symbol'],
                'decimals' => $currencyMeta['decimals'],
            ],
            'pending_payout_minor' => $pending,
            'nextPayout' => $nextPayout,
            'bankMask' => $bankMask,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $driverId = (int) $request->user()->id;
        $currency = MoneyPresenter::currency(DriverCurrency::resolve($request->user(), $request->header('X-Country')));
        $cursor = $request->query('cursor') !== null ? (string) $request->query('cursor') : null;
        $limit = Cursor::limit($request->query('limit'));

        $result = $this->statement->transactions(OwnerType::DRIVER, $driverId, $currency['code'], Cursor::decode($cursor), $limit);

        $items = array_map(function (array $e) use ($currency) {
            $signedMinor = ($e['direction'] === 'credit' ? 1 : -1) * (int) $e['amount_minor'];

            return [
                'id' => (int) $e['id'],
                'amount' => MoneyPresenter::decimal($signedMinor, $currency['decimals']),
                'status' => 'active',
                'transaction_type' => $e['kind'],
                'description' => $e['description'],
                'created_at' => $e['at'],
            ];
        }, $result['data']);

        $next = $result['meta']['next_cursor'] ?? null;

        return Reply::ok([
            'items' => $items,
            'nextCursor' => $next !== null ? Cursor::encode((int) $next) : null,
        ]);
    }

    public function dues(Request $request): JsonResponse
    {
        $driverId = (int) $request->user()->id;
        $requested = $request->filled('currency_code') ? (string) $request->query('currency_code') : null;
        $currencyMeta = MoneyPresenter::currency($requested ?? DriverCurrency::resolve($request->user(), $request->header('X-Country')));
        $currency = $currencyMeta['code'];

        $duesMinor = $this->dues->outstanding($driverId, $currency);
        $balanceMinor = $this->wallet->walletBalanceMinor(OwnerType::DRIVER, $driverId, $currency);

        return Reply::ok([
            'dues_minor' => $duesMinor,
            'dues' => MoneyPresenter::decimal($duesMinor, $currencyMeta['decimals']),
            'wallet_balance_minor' => $balanceMinor,
            'settleable_minor' => min($duesMinor, $balanceMinor),
            'currency_code' => $currency,
            'symbol' => $currencyMeta['symbol'],
            'decimals' => $currencyMeta['decimals'],
        ]);
    }

    public function settleDues(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount_minor' => ['nullable', 'integer', 'min:1'],
            'currency_code' => ['nullable', 'string', 'max:4'],
        ]);

        $driverId = (int) $request->user()->id;
        $currency = MoneyPresenter::currency($data['currency_code'] ?? DriverCurrency::resolve($request->user(), $request->header('X-Country')))['code'];
        // Without a client key, derive one from the CURRENT dues level so two
        // rapid taps against the same debt share a key and settle once — after
        // which dues=0 makes any retry a safe 422 no_dues.
        $key = $request->header('Idempotency-Key')
            ?: 'dues_settle:' . $driverId . ':' . $currency . ':' . $this->dues->outstanding($driverId, $currency);

        try {
            $result = $this->dues->settleFromWallet($driverId, $data['amount_minor'] ?? null, $currency, $key);
        } catch (\RuntimeException $e) {
            $code = $e->getMessage();

            return Reply::fail($code, $code, 422);
        }

        return Reply::ok($result);
    }

    public function payout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount_minor' => ['required', 'integer', 'min:1'],
            'currency_code' => ['nullable', 'string', 'max:4'],
            'source_account' => ['nullable', 'string', 'in:wallet,revenue'],
        ]);

        $driverId = (int) $request->user()->id;
        $currency = MoneyPresenter::currency($data['currency_code'] ?? DriverCurrency::resolve($request->user(), $request->header('X-Country')))['code'];
        $payout = $this->payouts->request(
            OwnerType::DRIVER,
            $driverId,
            $data['source_account'] ?? 'wallet',
            (int) $data['amount_minor'],
            $currency,
        );

        return Reply::ok([
            'id' => (int) $payout->id,
            'amount_minor' => (int) $payout->amount_minor,
            'currency_code' => $payout->currency_code ?? $currency,
            'status' => $payout->status,
            'source_account' => $payout->source_account ?? ($data['source_account'] ?? 'wallet'),
        ], 201);
    }
}
