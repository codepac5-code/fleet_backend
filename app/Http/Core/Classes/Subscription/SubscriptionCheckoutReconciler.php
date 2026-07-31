<?php

namespace App\Http\Core\Classes\Subscription;

use App\Models\OfficeSubscription;
use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\Stripe;
use Stripe\Subscription;
use Throwable;

/**
 * Turns a completed Stripe checkout into a subscription without waiting for a
 * webhook.
 *
 * Activation used to depend entirely on Stripe reaching
 * `POST /webhooks/subscriptions/stripe`. Behind a laptop, a firewall, or a
 * misconfigured endpoint secret that call never arrives, so an office paid, was
 * returned to a page saying "your plan will activate momentarily", and nothing
 * ever did. Reading the session back on return closes the loop from our side;
 * the webhook stays the primary path and both are idempotent, keyed on the
 * provider subscription id.
 */
class SubscriptionCheckoutReconciler
{
    public function __construct(
        private StripeSubscriptionWebhookGateway $gateway,
        private SubscriptionWebhookService $subscriptions,
        private SubscriptionRevenueService $revenue
    ) {
    }

    public function fromSession(string $sessionId, int $officeId): array
    {
        if ($sessionId === '' || $officeId <= 0) {
            return ['handled' => false, 'reason' => 'incomplete_request'];
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve(['id' => $sessionId, 'expand' => ['subscription']]);
        } catch (Throwable $e) {
            return ['handled' => false, 'reason' => 'unreadable_session'];
        }

        // The session id travels in a URL the office can edit, so it is only
        // ever applied to the office Stripe says paid for it.
        $owner = (int) ($session->client_reference_id ?? ($session->metadata->office_id ?? 0));

        if ($owner !== $officeId) {
            return ['handled' => false, 'reason' => 'foreign_session'];
        }

        if ((string) ($session->status ?? '') !== 'complete') {
            return ['handled' => false, 'reason' => 'incomplete_checkout'];
        }

        $result = $this->subscriptions->apply($this->gateway->normalize('checkout.session.completed', $session));

        $subscription = $session->subscription ?? null;

        // The session says a subscription exists; the subscription itself says
        // whether it is trialing or already being billed, and until when.
        if (is_object($subscription) && isset($subscription->id)) {
            $this->subscriptions->apply($this->gateway->normalize('customer.subscription.updated', $subscription));
        }

        return $result;
    }

    /**
     * Recover a payment whose checkout session is long gone — the office paid,
     * the webhook never landed, and nobody kept the session id.
     *
     * Stripe carries `office_id` and `plan_key` in the subscription metadata we
     * set at checkout, so the subscription object alone is enough to rebuild the
     * record. Idempotent: an existing row for this provider id is reused.
     */
    public function fromProviderSubscription($subscription): array
    {
        $officeId = (int) ($subscription->metadata->office_id ?? 0);
        $planKey = (string) ($subscription->metadata->plan_key ?? '');

        if ($officeId <= 0 || $planKey === '') {
            return ['handled' => false, 'reason' => 'unlabelled_subscription'];
        }

        $this->subscriptions->apply([
            'handled' => true,
            'type' => 'checkout.session.completed',
            'country' => $subscription->metadata->country ?? null,
            'office_id' => $officeId,
            'plan_key' => $planKey,
            'provider_customer_id' => isset($subscription->customer) ? (string) $subscription->customer : null,
            'provider_subscription_id' => (string) $subscription->id,
            'currency' => isset($subscription->currency) ? strtoupper((string) $subscription->currency) : null,
        ]);

        $result = $this->subscriptions->apply($this->gateway->normalize('customer.subscription.updated', $subscription));

        $this->bookPaidInvoices((string) $subscription->id);

        return $result;
    }

    /**
     * Book what this subscription has actually been paid.
     *
     * Revenue normally arrives with the `invoice.paid` webhook; when that never
     * landed, the money exists at Stripe and nowhere in our books. Reading the
     * paid invoices back posts each one — idempotent on the invoice id, so a
     * later webhook for the same invoice changes nothing.
     */
    private function bookPaidInvoices(string $subscriptionId): void
    {
        if ($subscriptionId === '') {
            return;
        }

        $row = OfficeSubscription::query()
            ->where('provider_subscription_id', $subscriptionId)
            ->orderByDesc('id')
            ->first();

        if ($row === null) {
            return;
        }

        try {
            $invoices = Invoice::all(['subscription' => $subscriptionId, 'status' => 'paid', 'limit' => 100]);
        } catch (Throwable $e) {
            return;
        }

        foreach ($invoices->autoPagingIterator() as $invoice) {
            $this->revenue->recordForSubscription(
                $row,
                (int) ($invoice->amount_paid ?? 0),
                isset($invoice->id) ? (string) $invoice->id : null,
                isset($invoice->currency) ? strtoupper((string) $invoice->currency) : null
            );
        }
    }

    /**
     * Rebuild every Stripe subscription sold in one country. The caller has
     * already activated that country's database; a subscription is only ever
     * written into the country its metadata names.
     *
     * @return array{applied:int, skipped:int, error:string|null}
     */
    public function syncCountry(string $countryCode): array
    {
        $country = strtoupper(trim($countryCode));
        $applied = 0;
        $skipped = 0;

        if ($country === '' || ! config('services.stripe.secret')) {
            return ['applied' => 0, 'skipped' => 0, 'error' => 'stripe_not_configured'];
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $subscriptions = Subscription::all(['limit' => 100, 'status' => 'all']);
        } catch (Throwable $e) {
            return ['applied' => 0, 'skipped' => 0, 'error' => $e->getMessage()];
        }

        foreach ($subscriptions->autoPagingIterator() as $subscription) {
            if (strtoupper((string) ($subscription->metadata->country ?? '')) !== $country) {
                continue;
            }

            try {
                $result = $this->fromProviderSubscription($subscription);
            } catch (Throwable $e) {
                $skipped++;
                continue;
            }

            ($result['handled'] ?? false) === true ? $applied++ : $skipped++;
        }

        return ['applied' => $applied, 'skipped' => $skipped, 'error' => null];
    }
}
