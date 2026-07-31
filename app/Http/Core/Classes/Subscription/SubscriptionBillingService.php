<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\SubscriptionPlan;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class SubscriptionBillingService
{
    public function __construct(private OfficeSubscriptionService $subscriptions)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * @param bool $chargeNow end any running trial and bill immediately, for an
     *                        office that asked to start paying early
     */
    public function createCheckoutSession(int $officeId, string $planKey, string $successUrl, string $cancelUrl, ?string $customerEmail = null, bool $chargeNow = false): string
    {
        if (!PlanKey::exists($planKey)) {
            throw new RuntimeException('unknown subscription plan: ' . $planKey);
        }

        $plan = SubscriptionPlan::query()->where('key', $planKey)->first();
        $catalog = PlanKey::plan($planKey);

        $priceMinor = (int) ($plan->price_minor ?? $catalog['price_minor'] ?? 0);

        if ($priceMinor <= 0) {
            throw new RuntimeException('plan ' . $planKey . ' is not self-service purchasable');
        }

        $currency = strtolower((string) ($plan->currency_code ?? ShardManager::currency()));
        // Never hand out a fresh trial to an office that is already inside one:
        // Stripe is told only the days actually left, so billing starts exactly
        // when the trial was always going to end — or today, if it asked to pay
        // now. A first-time subscriber still gets the full trial.
        $trialDays = $chargeNow ? 0 : $this->checkoutTrialDays($officeId, $planKey);
        $country = (string) (optional(ShardManager::current())->country_code ?? '');
        $name = (string) ($plan->name ?? $catalog['name'] ?? ucfirst($planKey));

        $metadata = [
            'plan_key' => $planKey,
            'office_id' => (string) $officeId,
            'country' => $country,
        ];

        $params = [
            'mode' => 'subscription',
            'client_reference_id' => (string) $officeId,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => $metadata,
            'subscription_data' => array_filter([
                // Stripe rejects trial_period_days: 0 — the field must be absent
                // for the card to be charged straight away.
                'trial_period_days' => $trialDays > 0 ? $trialDays : null,
                'metadata' => $metadata,
            ], fn ($value) => $value !== null),
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $priceMinor,
                    'recurring' => ['interval' => 'month'],
                    'product_data' => ['name' => $name . ' plan'],
                ],
            ]],
        ];

        if ($customerEmail !== null && $customerEmail !== '') {
            $params['customer_email'] = $customerEmail;
        }

        $session = Session::create($params);

        return (string) $session->url;
    }

    /**
     * The trial length this checkout should carry: what is left of a running
     * trial, otherwise the plan's own trial for an office that never had one.
     */
    public function checkoutTrialDays(int $officeId, string $planKey): int
    {
        $remaining = $this->subscriptions->remainingTrialDays($officeId);

        if ($remaining > 0) {
            return $remaining;
        }

        return $this->subscriptions->hasUsedTrial($officeId) ? 0 : $this->subscriptions->trialDaysFor($planKey);
    }

    public function cancelAtPeriodEnd(string $subscriptionId): void
    {
        \Stripe\Subscription::update($subscriptionId, ['cancel_at_period_end' => true]);
    }
}
