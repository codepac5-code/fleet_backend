<?php

namespace App\Http\Core\Classes\Account;

interface CardGateway
{
    public function setupIntent(int $userId): array;

    public function describe(string $token): ?array;

    /**
     * Create a PaymentIntent to charge the rider (wallet top-up or a trip fare).
     * `$metadata` is merged onto the intent's metadata so the gateway dashboard
     * shows what the charge was for (e.g. `purpose`, `booking_id`).
     * `$manualCapture` holds the funds without taking them (an authorization) so
     * the final amount can be captured later — the card pre-auth for a ride.
     * Returns `['id' => pi_…, 'clientSecret' => …, 'status' => …, 'requiresAction' => bool]`.
     */
    public function paymentIntent(int $userId, int $amountMinor, string $currency, ?int $paymentMethodId, string $idempotencyKey, array $metadata = [], bool $manualCapture = false): array;

    /**
     * Capture (settle) an authorized PaymentIntent for `$amountToCaptureMinor`
     * (≤ the authorized amount; the gateway releases any difference). Returns the
     * resulting status, e.g. 'succeeded'.
     */
    public function capturePaymentIntent(string $paymentIntentId, int $amountToCaptureMinor): string;

    /** Void an uncaptured authorization, releasing the hold on the rider's card. */
    public function cancelPaymentIntent(string $paymentIntentId): void;

    /**
     * Current status of a PaymentIntent straight from the gateway (e.g.
     * 'succeeded', 'processing', 'requires_payment_method'), or null if it
     * can't be read. Lets the backend confirm a top-up on demand instead of
     * waiting for the async webhook — needed anywhere the webhook can't reach
     * the server (local dev) and as a faster path in production.
     */
    public function paymentIntentStatus(string $paymentIntentId): ?string;
}
