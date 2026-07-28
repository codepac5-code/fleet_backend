<?php

namespace App\Http\Core\Classes\Subscription\Billing;

/**
 * Default gateway: no outward push. The closed invoice waits for a staff member
 * to confirm collection from the panel. Used in commission regions and wherever
 * no provider is wired.
 */
class ManualOverageBillingGateway implements OverageBillingGateway
{
    public function bill(array $invoice, ?string $customerId, ?string $subscriptionId): array
    {
        return ['billed' => false, 'method' => 'manual', 'external_ref' => null, 'reason' => 'manual'];
    }
}
