<?php

namespace App\Http\Core\Classes\Subscription\Billing;

use Stripe\InvoiceItem;
use Stripe\Stripe;

/**
 * Real Stripe-SDK implementation: creates a one-off invoice item on the office's
 * customer so it lands on their next subscription invoice. Amounts are already in
 * minor units, which is what Stripe expects. Bind this over the interface (and
 * StripeOverageBillingGateway over OverageBillingGateway) once Stripe billing is
 * configured for a region; it is intentionally NOT the default binding.
 */
class StripeSdkInvoiceItemClient implements StripeInvoiceItemClient
{
    public function createInvoiceItem(string $customerId, ?string $subscriptionId, int $amountMinor, string $currency, string $description): string
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $params = [
            'customer' => $customerId,
            'amount' => $amountMinor,
            'currency' => strtolower($currency),
            'description' => $description,
        ];

        if ($subscriptionId !== null && $subscriptionId !== '') {
            $params['subscription'] = $subscriptionId;
        }

        return (string) InvoiceItem::create($params)->id;
    }
}
