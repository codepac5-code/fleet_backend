<?php

namespace App\Http\Core\Classes\Subscription\Billing;

/**
 * Hands a closed overage invoice to a payment provider for collection. The
 * closeout stamps our ledger; this pushes the receivable outward (e.g. a Stripe
 * invoice item on the office's next subscription invoice). Implementations MUST
 * be side-effect-safe to call best-effort — a provider hiccup never rolls back a
 * closed invoice.
 */
interface OverageBillingGateway
{
    /**
     * @param  array{invoice_ref:string,office_id:int,period:string,total_minor:int,currency:string}  $invoice
     * @return array{billed:bool,method:string,external_ref:?string,reason:?string}
     */
    public function bill(array $invoice, ?string $customerId, ?string $subscriptionId): array;
}
