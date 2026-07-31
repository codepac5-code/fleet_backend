<?php

namespace App\Http\Core\Classes\Subscription;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Models\OfficeSubscription;
use Illuminate\Support\Carbon;
use Throwable;

class SubscriptionWebhookService
{
    private const STRIPE_STATUS_MAP = [
        'trialing' => SubscriptionStatus::TRIALING,
        'active' => SubscriptionStatus::ACTIVE,
        'past_due' => SubscriptionStatus::PAST_DUE,
        'unpaid' => SubscriptionStatus::PAST_DUE,
        'canceled' => SubscriptionStatus::CANCELED,
        'incomplete_expired' => SubscriptionStatus::ENDED,
    ];

    private const EVENT_FOR_STATUS = [
        SubscriptionStatus::ACTIVE => EventType::SUBSCRIPTION_ACTIVATED,
        SubscriptionStatus::PAST_DUE => EventType::SUBSCRIPTION_PAST_DUE,
        SubscriptionStatus::CANCELED => EventType::SUBSCRIPTION_CANCELED,
    ];

    public function __construct(
        private ?EventBus $events = null,
        private ?OfficeSubscriptionService $subscriptions = null,
        private ?PlanOverageService $overage = null,
        private ?SubscriptionRevenueService $revenue = null
    ) {
    }

    public function apply(array $event): array
    {
        if (($event['type'] ?? '') === 'checkout.session.completed') {
            return $this->activateFromCheckout($event);
        }

        $providerSubscriptionId = (string) ($event['provider_subscription_id'] ?? '');

        if ($providerSubscriptionId === '') {
            return ['handled' => false, 'reason' => 'missing_subscription_id'];
        }

        $subscription = OfficeSubscription::query()
            ->where('provider_subscription_id', $providerSubscriptionId)
            ->orderByDesc('id')
            ->first();

        if ($subscription === null) {
            return ['handled' => false, 'reason' => 'unknown_subscription'];
        }

        $before = $subscription->status;
        $newStatus = $this->resolveStatus($event, $before);

        $subscription->status = $newStatus;

        if (array_key_exists('trial_end', $event)) {
            $subscription->trial_ends_at = $event['trial_end'] === null
                ? null
                : Carbon::createFromTimestamp((int) $event['trial_end'], config('app.timezone', 'UTC'));
        }

        if (isset($event['current_period_end']) && $event['current_period_end'] !== null) {
            $subscription->current_period_end = Carbon::createFromTimestamp(
                (int) $event['current_period_end'],
                config('app.timezone', 'UTC')
            );
        }

        if (array_key_exists('cancel_at_period_end', $event) && $event['cancel_at_period_end'] !== null) {
            $subscription->cancel_at_period_end = (bool) $event['cancel_at_period_end'];
        }

        $subscription->save();

        $changed = $before !== $newStatus;

        if ($changed) {
            $this->emit($subscription, $newStatus);
        }

        // A paid renewal closes the billing cycle → roll every fully-elapsed
        // period's accrued overage into its invoice. Best-effort; a collection
        // hiccup must never fail the renewal webhook.
        // A paid invoice is the moment subscription money exists. Book it, or
        // the platform's whole income in a subscription country stays invisible
        // to every report.
        if (($event['type'] ?? '') === 'invoice.paid' && $this->revenue !== null) {
            $this->revenue->recordForSubscription(
                $subscription,
                (int) ($event['amount_paid_minor'] ?? 0),
                $event['provider_invoice_id'] ?? null,
                $event['currency'] ?? null
            );
        }

        if (($event['type'] ?? '') === 'invoice.paid' && $this->overage !== null) {
            try {
                // Order matters: overage already pushed to Stripe rode along on
                // THIS invoice, so it is collected now — while the period being
                // closed below bills on the NEXT one.
                $this->overage->markStripeCollectedForOffice((int) $subscription->office_id);
                $this->overage->closeElapsedForOffice((int) $subscription->office_id);
            } catch (Throwable $e) {
            }
        }

        return [
            'handled' => true,
            'office_id' => (int) $subscription->office_id,
            'status' => $newStatus,
            'changed' => $changed,
        ];
    }

    private function activateFromCheckout(array $event): array
    {
        $officeId = (int) ($event['office_id'] ?? 0);
        $planKey = (string) ($event['plan_key'] ?? '');

        if ($officeId <= 0 || $planKey === '' || $this->subscriptions === null) {
            return ['handled' => false, 'reason' => 'incomplete_checkout'];
        }

        $subscription = $this->subscriptions->beginFromProvider(
            $officeId,
            $planKey,
            $event['currency'] ?? null,
            $event['provider_customer_id'] ?? null,
            $event['provider_subscription_id'] ?? null
        );

        $this->emitEvent($subscription, EventType::SUBSCRIPTION_ACTIVATED);

        return [
            'handled' => true,
            'office_id' => $officeId,
            'status' => $subscription->status,
            'created' => true,
        ];
    }

    private function resolveStatus(array $event, string $current): string
    {
        switch ($event['type'] ?? '') {
            case 'invoice.paid':
                return SubscriptionStatus::ACTIVE;
            case 'invoice.payment_failed':
                return SubscriptionStatus::PAST_DUE;
            case 'customer.subscription.deleted':
                return SubscriptionStatus::CANCELED;
            case 'customer.subscription.updated':
                $stripeStatus = (string) ($event['status'] ?? '');

                return self::STRIPE_STATUS_MAP[$stripeStatus] ?? $current;
            default:
                return $current;
        }
    }

    private function emit(OfficeSubscription $subscription, string $status): void
    {
        if (!isset(self::EVENT_FOR_STATUS[$status])) {
            return;
        }

        $this->emitEvent($subscription, self::EVENT_FOR_STATUS[$status]);
    }

    private function emitEvent(OfficeSubscription $subscription, string $eventType): void
    {
        if ($this->events === null) {
            return;
        }

        // The fleet desk has to hear a failed renewal too: the sweep already
        // told it about expired trials, while a provider-reported failure went
        // only to the office — the one party that cannot chase itself.
        $this->events->emit(new DomainEvent(
            $eventType,
            [Channel::office((int) $subscription->office_id), Channel::admin()],
            [
                'office_id' => (int) $subscription->office_id,
                'plan_key' => $subscription->plan_key,
                'status' => $subscription->status,
                'current_period_end' => optional($subscription->current_period_end)->toIso8601String(),
            ]
        ));
    }
}
