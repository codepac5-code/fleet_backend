<?php

namespace App\Http\Core\Classes\Payment\Gateway;

use App\Http\Core\Const\Payment\PaymentStatus;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Throwable;

class StripeGateway implements PaymentGateway
{
    private const STATUS_MAP = [
        'payment_intent.succeeded' => PaymentStatus::SUCCEEDED,
        'payment_intent.payment_failed' => PaymentStatus::FAILED,
        'charge.refunded' => PaymentStatus::REFUNDED,
    ];

    public function verifyAndNormalize(Request $request): ?array
    {
        $secret = config('services.stripe.webhook_secret', env('STRIPE_WEBHOOK_SECRET'));
        $signature = $request->header('Stripe-Signature');

        if (!$secret || !$signature) {
            return null;
        }

        try {
            $event = Webhook::constructEvent($request->getContent(), $signature, $secret);
        } catch (Throwable $e) {
            return null;
        }

        $type = $event->type ?? '';

        if (!isset(self::STATUS_MAP[$type])) {
            return ['handled' => false, 'idempotency_key' => '', 'status' => '', 'provider_ref' => null];
        }

        $object = $event->data->object ?? null;
        $idempotencyKey = $object->metadata->idempotency_key ?? '';

        return [
            'handled' => true,
            'idempotency_key' => (string) $idempotencyKey,
            'status' => self::STATUS_MAP[$type],
            'provider_ref' => isset($object->id) ? (string) $object->id : null,
        ];
    }
}
