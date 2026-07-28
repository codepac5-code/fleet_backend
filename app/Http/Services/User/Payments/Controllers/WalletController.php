<?php

namespace App\Http\Services\User\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Payments\Logic\WalletService;
use App\Http\Services\User\Payments\Requests\TopUpRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallet)
    {
    }

    public function balance(Request $request): JsonResponse
    {
        return Reply::ok($this->wallet->balance((int) $request->user()->id, $this->currency($request)));
    }

    public function transactions(Request $request): JsonResponse
    {
        return Reply::ok($this->wallet->transactions(
            (int) $request->user()->id,
            $this->currency($request),
            $request->query('cursor') !== null ? (string) $request->query('cursor') : null,
            $request->query('limit')
        ));
    }

    public function topUpOptions(Request $request): JsonResponse
    {
        return Reply::ok($this->wallet->topUpOptions());
    }

    public function topUpQuote(Request $request): JsonResponse
    {
        return Reply::ok($this->wallet->topUpQuote(
            $this->currency($request),
            (int) $request->query('amount', 0)
        ));
    }

    public function topUp(TopUpRequest $request): JsonResponse
    {
        $data = $request->validated();

        return Reply::ok($this->wallet->topUp(
            (int) $request->user()->id,
            $this->currency($request),
            (int) $data['amount'],
            isset($data['paymentMethodId']) ? (int) $data['paymentMethodId'] : null,
            (string) $request->header('Idempotency-Key', '')
        ));
    }

    public function verifyTopUp(Request $request): JsonResponse
    {
        $data = $request->validate(['paymentIntentId' => ['required', 'string']]);

        return Reply::ok($this->wallet->verifyTopUp((int) $request->user()->id, $data['paymentIntentId']));
    }

    private function currency(Request $request): ?string
    {
        return $request->query('currency_code') !== null ? (string) $request->query('currency_code') : null;
    }
}
