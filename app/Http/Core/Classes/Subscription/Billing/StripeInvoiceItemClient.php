<?php

namespace App\Http\Core\Classes\Subscription\Billing;

/**
 * Thin seam over the Stripe SDK's invoice-item creation, so the gateway is unit-
 * testable with a fake. The real call attaches a one-off charge to the customer's
 * next subscription invoice. Kept deliberately minimal — one method, no state.
 */
interface StripeInvoiceItemClient
{
    /** @return string the created invoice-item id. */
    public function createInvoiceItem(string $customerId, ?string $subscriptionId, int $amountMinor, string $currency, string $description): string;
}
