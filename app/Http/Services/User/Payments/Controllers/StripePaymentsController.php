<?php

namespace App\Http\Services\User\Payments\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\User\Payments\Logic\PaymentMethodService;
use App\Http\Services\User\Payments\Logic\WalletService;
use App\Http\Services\User\Payments\Requests\PaymentIntentRequest;
use App\Http\Services\User\Support\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripePaymentsController extends Controller
{
    public function __construct(
        private PaymentMethodService $methods,
        private WalletService $wallet
    ) {
    }

    public function setupIntent(Request $request): JsonResponse
    {
        return Reply::ok($this->methods->setupIntent((int) $request->user()->id));
    }

    public function paymentIntent(PaymentIntentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->wallet->topUp(
            (int) $request->user()->id,
            $request->query('currency_code') !== null ? (string) $request->query('currency_code') : null,
            (int) $data['amount'],
            isset($data['paymentMethodId']) ? (int) $data['paymentMethodId'] : null,
            (string) $request->header('Idempotency-Key', '')
        );

        return Reply::ok([
            'paymentIntentId' => $result['paymentIntentId'] ?? $result['clientSecret'],
            'clientSecret' => $result['clientSecret'],
            'requiresAction' => $result['requiresAction'],
            'status' => $result['status'],
        ]);
    }
}
