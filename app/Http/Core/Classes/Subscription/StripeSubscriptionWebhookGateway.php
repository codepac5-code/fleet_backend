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
                'provider_subscription_id' => $this->invoiceSubscription($object),
                'current_period_end' => $object->lines->data[0]->period->end ?? null,
                // What was actually collected — the ledger has to book it.
                'provider_invoice_id' => isset($object->id) ? (string) $object->id : null,
                'amount_paid_minor' => isset($object->amount_paid) ? (int) $object->amount_paid : 0,
                'currency' => isset($object->currency) ? strtoupper((string) $object->currency) : null,
            ];
        }

        return [
            'handled' => true,
            'type' => $type,
            'country' => $this->country($object),
            'provider_subscription_id' => isset($object->id) ? (string) $object->id : '',
            'status' => isset($object->status) ? (string) $object->status : null,
            // Stripe is the authority on when the trial ends — an office that
            // paid early has no trial left, and the record must say so.
            'trial_end' => $object->trial_end ?? null,
            'current_period_end' => $this->periodEnd($object),
            'cancel_at_period_end' => $object->cancel_at_period_end ?? null,
        ];
    }

    /**
     * The subscription an invoice belongs to. Stripe moved this off the invoice
     * and under `parent.subscription_details`, the same way it moved the period
     * end onto the items; reading one place only is how a renewal silently
     * stops matching any subscription we hold.
     */
    private function invoiceSubscription($object): string
    {
        $candidates = [
            $object->subscription ?? null,
            $object->parent->subscription_details->subscription ?? null,
            $object->lines->data[0]->parent->subscription_item_details->subscription ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }

            if (is_object($candidate) && isset($candidate->id)) {
                return (string) $candidate->id;
            }
        }

        return '';
    }

    /**
     * When the current billing period ends.
     *
     * Stripe moved this off the subscription and onto its items: reading only
     * the top-level field returned null on the current API version, so the
     * renewal date never advanced and the panel told a subscriber it renews
     * today, every day.
     */
    private function periodEnd($object)
    {
        return $object->current_period_end
            ?? $object->items->data[0]->current_period_end
            ?? null;
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
