<?php

namespace App\Http\Core\Classes\Subscription\Billing;

use Throwable;

/**
 * Pushes a closed overage invoice to Stripe as an invoice item on the office's
 * next subscription invoice. Requires the office's Stripe customer id (from its
 * OfficeSubscription); falls back to 'manual' when it is missing or the SDK call
 * fails, so collection degrades to the staff-confirmation path instead of losing
 * the receivable.
 */
class StripeOverageBillingGateway implements OverageBillingGateway
{
    public function __construct(private StripeInvoiceItemClient $client)
    {
    }

    public function bill(array $invoice, ?string $customerId, ?string $subscriptionId): array
    {
        if ($customerId === null || $customerId === '') {
            return ['billed' => false, 'method' => 'manual', 'external_ref' => null, 'reason' => 'no_stripe_customer'];
        }

        try {
            $externalRef = $this->client->createInvoiceItem(
                $customerId,
                $subscriptionId,
                (int) $invoice['total_minor'],
                (string) $invoice['currency'],
                sprintf('Plan overage %s (%s)', $invoice['period'], $invoice['invoice_ref']),
            );

            return ['billed' => true, 'method' => 'stripe', 'external_ref' => $externalRef, 'reason' => null];
        } catch (Throwable $e) {
            return ['billed' => false, 'method' => 'manual', 'external_ref' => null, 'reason' => 'stripe_error'];
        }
    }
}
