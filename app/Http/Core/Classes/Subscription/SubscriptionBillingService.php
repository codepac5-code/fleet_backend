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

    public function createCheckoutSession(int $officeId, string $planKey, string $successUrl, string $cancelUrl, ?string $customerEmail = null): string
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
        $trialDays = $this->subscriptions->trialDaysFor($planKey);
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
            'subscription_data' => [
                'trial_period_days' => $trialDays,
                'metadata' => $metadata,
            ],
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

    public function cancelAtPeriodEnd(string $subscriptionId): void
    {
        \Stripe\Subscription::update($subscriptionId, ['cancel_at_period_end' => true]);
    }
}
