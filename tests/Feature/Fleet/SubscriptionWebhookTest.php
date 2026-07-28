<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Subscription\OfficeSubscriptionService;
use App\Http\Core\Classes\Subscription\StripeSubscriptionWebhookGateway;
use App\Http\Core\Classes\Subscription\SubscriptionWebhookService;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Models\EventOutbox;
use App\Models\OfficeSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class SubscriptionWebhookTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_25_000001_create_subscription_plans_table.php',
        '2026_07_13_000005_add_trial_days_to_subscription_plans.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_07_13_000006_add_billing_lifecycle_to_office_subscriptions.php',
        '2026_06_25_000007_create_event_outbox_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $prev = DB::getDefaultConnection();
        DB::setDefaultConnection('global');
        foreach (PlanKey::CATALOG as $key => $p) {
            SubscriptionPlan::query()->create([
                'key' => $key, 'name' => $p['name'], 'price_minor' => $p['price_minor'],
                'fleet_commission_rate' => $p['fleet_rate'], 'driver_limit' => $p['driver_limit'],
                'is_active' => true, 'sort' => $p['sort'],
            ]);
        }
        DB::setDefaultConnection($prev);
    }

    private function seedSub(string $subId = 'sub_1', string $status = SubscriptionStatus::TRIALING): OfficeSubscription
    {
        return OfficeSubscription::query()->create([
            'office_id' => 7,
            'plan_key' => 'business',
            'fleet_commission_rate' => 12.0,
            'office_commission_rate' => 0.0,
            'price_minor' => 35000,
            'currency_code' => 'USD',
            'status' => $status,
            'provider' => 'stripe',
            'provider_subscription_id' => $subId,
        ]);
    }

    public function test_invoice_paid_activates_and_extends_period(): void
    {
        $this->seedSub();
        $service = new SubscriptionWebhookService();

        $result = $service->apply([
            'type' => 'invoice.paid',
            'provider_subscription_id' => 'sub_1',
            'current_period_end' => 1893456000,
        ]);

        $this->assertTrue($result['handled']);
        $this->assertSame(SubscriptionStatus::ACTIVE, $result['status']);
        $sub = OfficeSubscription::query()->where('provider_subscription_id', 'sub_1')->first();
        $this->assertSame(SubscriptionStatus::ACTIVE, $sub->status);
        $this->assertSame(1893456000, $sub->current_period_end->timestamp);
    }

    public function test_payment_failed_sets_past_due_and_emits_event(): void
    {
        $this->seedSub('sub_2', SubscriptionStatus::ACTIVE);
        $service = new SubscriptionWebhookService(new EventBus());

        $result = $service->apply([
            'type' => 'invoice.payment_failed',
            'provider_subscription_id' => 'sub_2',
        ]);

        $this->assertSame(SubscriptionStatus::PAST_DUE, $result['status']);
        $this->assertTrue($result['changed']);
        $this->assertSame(1, EventOutbox::query()->where('type', EventType::SUBSCRIPTION_PAST_DUE)->count());
    }

    public function test_subscription_deleted_cancels(): void
    {
        $this->seedSub('sub_3', SubscriptionStatus::ACTIVE);
        $service = new SubscriptionWebhookService();

        $result = $service->apply(['type' => 'customer.subscription.deleted', 'provider_subscription_id' => 'sub_3']);

        $this->assertSame(SubscriptionStatus::CANCELED, $result['status']);
    }

    public function test_subscription_updated_maps_stripe_status(): void
    {
        $this->seedSub('sub_4', SubscriptionStatus::TRIALING);
        $service = new SubscriptionWebhookService();

        $service->apply([
            'type' => 'customer.subscription.updated',
            'provider_subscription_id' => 'sub_4',
            'status' => 'active',
            'cancel_at_period_end' => true,
        ]);

        $sub = OfficeSubscription::query()->where('provider_subscription_id', 'sub_4')->first();
        $this->assertSame(SubscriptionStatus::ACTIVE, $sub->status);
        $this->assertTrue((bool) $sub->cancel_at_period_end);
    }

    public function test_unknown_subscription_is_not_handled(): void
    {
        $service = new SubscriptionWebhookService();

        $result = $service->apply(['type' => 'invoice.paid', 'provider_subscription_id' => 'sub_nope']);

        $this->assertFalse($result['handled']);
        $this->assertSame('unknown_subscription', $result['reason']);
    }

    public function test_invoice_paid_is_idempotent(): void
    {
        $this->seedSub('sub_5', SubscriptionStatus::ACTIVE);
        $service = new SubscriptionWebhookService(new EventBus());

        $first = $service->apply(['type' => 'invoice.paid', 'provider_subscription_id' => 'sub_5']);
        $second = $service->apply(['type' => 'invoice.paid', 'provider_subscription_id' => 'sub_5']);

        $this->assertFalse($first['changed']);
        $this->assertFalse($second['changed']);
        $this->assertSame(SubscriptionStatus::ACTIVE, $second['status']);
        $this->assertSame(0, EventOutbox::query()->count());
    }

    public function test_checkout_completed_creates_trialing_subscription(): void
    {
        $service = new SubscriptionWebhookService(new EventBus(), new OfficeSubscriptionService());

        $result = $service->apply([
            'type' => 'checkout.session.completed',
            'office_id' => 42,
            'plan_key' => PlanKey::BUSINESS,
            'provider_customer_id' => 'cus_1',
            'provider_subscription_id' => 'sub_new',
            'currency' => 'USD',
        ]);

        $this->assertTrue($result['handled']);
        $this->assertSame(SubscriptionStatus::TRIALING, $result['status']);

        $sub = OfficeSubscription::query()->where('office_id', 42)->first();
        $this->assertSame('sub_new', $sub->provider_subscription_id);
        $this->assertSame('cus_1', $sub->provider_customer_id);
        $this->assertSame(12.0, (float) $sub->fleet_commission_rate);
        $this->assertSame(1, EventOutbox::query()->where('type', EventType::SUBSCRIPTION_ACTIVATED)->count());
    }

    public function test_checkout_completed_is_idempotent_by_subscription_id(): void
    {
        $service = new SubscriptionWebhookService(null, new OfficeSubscriptionService());

        $event = [
            'type' => 'checkout.session.completed',
            'office_id' => 43, 'plan_key' => PlanKey::STARTER,
            'provider_customer_id' => 'cus_2', 'provider_subscription_id' => 'sub_dup', 'currency' => 'USD',
        ];
        $service->apply($event);
        $service->apply($event);

        $this->assertSame(1, OfficeSubscription::query()->where('provider_subscription_id', 'sub_dup')->count());
    }

    public function test_gateway_normalizes_invoice_and_subscription_shapes(): void
    {
        $gateway = new StripeSubscriptionWebhookGateway();

        $invoice = (object) [
            'subscription' => 'sub_9',
            'lines' => (object) ['data' => [(object) ['period' => (object) ['end' => 1893456000]]]],
        ];
        $inv = $gateway->normalize('invoice.paid', $invoice);
        $this->assertSame('sub_9', $inv['provider_subscription_id']);
        $this->assertSame(1893456000, $inv['current_period_end']);

        $subscription = (object) ['id' => 'sub_9', 'status' => 'past_due', 'current_period_end' => 1893456000, 'cancel_at_period_end' => false];
        $sub = $gateway->normalize('customer.subscription.updated', $subscription);
        $this->assertSame('sub_9', $sub['provider_subscription_id']);
        $this->assertSame('past_due', $sub['status']);
    }
}
