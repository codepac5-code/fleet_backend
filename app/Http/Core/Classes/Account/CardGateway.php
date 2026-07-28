<?php

namespace App\Http\Core\Classes\Account;

interface CardGateway
{
    public function setupIntent(int $userId): array;

    public function describe(string $token): ?array;

    /**
     * Create a PaymentIntent to charge the rider (e.g. wallet top-up). Returns
     * `['id' => pi_…, 'clientSecret' => …, 'status' => …, 'requiresAction' => bool]`.
     */
    public function paymentIntent(int $userId, int $amountMinor, string $currency, ?int $paymentMethodId, string $idempotencyKey): array;

    /**
     * Current status of a PaymentIntent straight from the gateway (e.g.
     * 'succeeded', 'processing', 'requires_payment_method'), or null if it
     * can't be read. Lets the backend confirm a top-up on demand instead of
     * waiting for the async webhook — needed anywhere the webhook can't reach
     * the server (local dev) and as a faster path in production.
     */
    public function paymentIntentStatus(string $paymentIntentId): ?string;
}
