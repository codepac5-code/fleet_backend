<?php

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Support\ApiResponse;
use App\Http\Core\Classes\Payment\Gateway\GatewayRegistry;
use App\Http\Core\Classes\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentWebhookController
{
    public function __construct(
        private PaymentService $payments,
        private GatewayRegistry $gateways
    ) {
    }

    public function handle(Request $request, string $provider): JsonResponse
    {
        $gateway = $this->gateways->for($provider);

        if ($gateway === null) {
            return ApiResponse::error('not_found', 'Unknown payment provider: ' . $provider, [], 404);
        }

        $normalized = $gateway->verifyAndNormalize($request);

        if ($normalized === null) {
            return ApiResponse::error('invalid_signature', 'Webhook signature verification failed.', [], 400);
        }

        if (($normalized['handled'] ?? false) !== true) {
            return ApiResponse::data(['ignored' => true]);
        }

        if ($normalized['idempotency_key'] === '' || $normalized['status'] === '') {
            return ApiResponse::error('validation_failed', 'idempotency_key and status are required.', [], 422);
        }

        try {
            $payment = $this->payments->handleGatewayEvent(
                $normalized['idempotency_key'],
                $normalized['status'],
                $normalized['provider_ref'] ?? null
            );
        } catch (Throwable $e) {
            return ApiResponse::error('webhook_error', $e->getMessage(), [], 422);
        }

        return ApiResponse::data([
            'uuid' => $payment->uuid,
            'status' => $payment->status,
            'ledger_transaction_uuid' => $payment->ledger_transaction_uuid,
        ]);
    }
}
