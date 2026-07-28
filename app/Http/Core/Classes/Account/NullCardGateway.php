<?php

namespace App\Http\Core\Classes\Account;

use App\Http\Core\Exceptions\DomainException;

/**
 * Bound when no card gateway is configured. The intent operations are
 * genuinely unavailable, but that is a 503 the client can act on
 * ("payments are temporarily unavailable"), NOT a 500: a bare RuntimeException
 * fell through Reply::fromException to `server_error`, so an unconfigured
 * gateway looked like a backend crash on the rider's top-up screen.
 */
class NullCardGateway implements CardGateway
{
    public function setupIntent(int $userId): array
    {
        throw DomainException::make('payments_unavailable', 503, 'Payments are temporarily unavailable.');
    }

    public function describe(string $token): ?array
    {
        return null;
    }

    public function paymentIntent(int $userId, int $amountMinor, string $currency, ?int $paymentMethodId, string $idempotencyKey): array
    {
        throw DomainException::make('payments_unavailable', 503, 'Payments are temporarily unavailable.');
    }

    public function paymentIntentStatus(string $paymentIntentId): ?string
    {
        return null;
    }
}
