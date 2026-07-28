<?php

namespace App\Http\Core\Classes\Subscription;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Throwable;

class StripeSubscriptionWebhookGateway
{
    private const HANDLED = [
        'checkout.session.completed',
        'invoice.paid',
        'invoice.payment_failed',
        'customer.subscription.updated',
        'customer.subscription.deleted',
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

        $type = (string) ($event->type ?? '');

        if (!in_array($type, self::HANDLED, true)) {
            return ['handled' => false];
        }

        return $this->normalize($type, $event->data->object ?? null);
    }

    public function normalize(string $type, $object): array
    {
        if ($object === null) {
            return ['handled' => false];
        }

        if ($type === 'checkout.session.completed') {
            return [
                'handled' => true,
                'type' => $type,
                'country' => $this->country($object),
                'office_id' => isset($object->client_reference_id) ? (int) $object->client_reference_id : 0,
                'plan_key' => $object->metadata->plan_key ?? '',
                'provider_customer_id' => isset($object->customer) ? (string) $object->customer : null,
                'provider_subscription_id' => isset($object->subscription) ? (string) $object->subscription : null,
                'currency' => isset($object->currency) ? strtoupper((string) $object->currency) : null,
            ];
        }

        if (str_starts_with($type, 'invoice.')) {
            return [
                'handled' => true,
                'type' => $type,
                'country' => $this->country($object),
                'provider_subscription_id' => isset($object->subscription) ? (string) $object->subscription : '',
                'current_period_end' => $object->lines->data[0]->period->end ?? null,
            ];
        }

        return [
            'handled' => true,
            'type' => $type,
            'country' => $this->country($object),
            'provider_subscription_id' => isset($object->id) ? (string) $object->id : '',
            'status' => isset($object->status) ? (string) $object->status : null,
            'current_period_end' => $object->current_period_end ?? null,
            'cancel_at_period_end' => $object->cancel_at_period_end ?? null,
        ];
    }

    private function country($object): ?string
    {
        $candidates = [
            $object->metadata->country ?? null,
            $object->subscription_details->metadata->country ?? null,
            $object->lines->data[0]->metadata->country ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== null && $candidate !== '') {
                return strtoupper((string) $candidate);
            }
        }

        return null;
    }
}
