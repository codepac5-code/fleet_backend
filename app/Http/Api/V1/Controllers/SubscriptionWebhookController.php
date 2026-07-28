<?php

namespace App\Http\Api\V1\Controllers;

use App\Http\Api\V1\Support\ApiResponse;
use App\Http\Core\Classes\Subscription\StripeSubscriptionWebhookGateway;
use App\Http\Core\Classes\Subscription\SubscriptionWebhookService;
use App\Http\Core\GeoServices\ShardManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SubscriptionWebhookController
{
    public function __construct(
        private StripeSubscriptionWebhookGateway $gateway,
        private SubscriptionWebhookService $subscriptions
    ) {
    }

    public function handle(Request $request, string $provider): JsonResponse
    {
        if ($provider !== 'stripe') {
            return ApiResponse::error('not_found', 'Unknown subscription provider: ' . $provider, [], 404);
        }

        $normalized = $this->gateway->verifyAndNormalize($request);

        if ($normalized === null) {
            return ApiResponse::error('invalid_signature', 'Webhook signature verification failed.', [], 400);
        }

        if (($normalized['handled'] ?? false) !== true) {
            return ApiResponse::data(['ignored' => true]);
        }

        $country = (string) ($normalized['country'] ?? '');
        $node = $country !== '' ? ShardManager::byCountryCode($country) : null;

        if ($node === null) {
            return ApiResponse::data(['ignored' => true, 'reason' => 'unresolved_country']);
        }

        ShardManager::activate($node);

        try {
            $result = $this->subscriptions->apply($normalized);
        } catch (Throwable $e) {
            return ApiResponse::error('webhook_error', $e->getMessage(), [], 422);
        }

        return ApiResponse::data($result);
    }
}
