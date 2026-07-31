<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Subscription\Billing\OverageBillingGateway;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Classes\Subscription\PlanUsageService;
use App\Http\Core\Const\Event\EventType;
use App\Models\EventOutbox;
use App\Models\OfficeSubscription;
use App\Models\PlanOverageCharge;

class PlanOverageTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_07_25_000003_create_plan_overage_charges_table.php',
        '2026_06_25_000002_create_office_subscriptions_table.php',
        '2026_07_13_000006_add_billing_lifecycle_to_office_subscriptions.php',
        '2026_06_25_000007_create_event_outbox_table.php',
    ];

    private PlanOverageService $overage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->overage = new PlanOverageService(new PlanUsageService());
    }

    public function test_driver_overage_accrues_once_and_is_idempotent(): void
    {
        $first = $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $second = $this->overage->recordDriverOverage(5, 99, 1000, 'USD');

        $this->assertNotNull($first);
        $this->assertNull($second, 'the same driver must not be charged twice');
        $this->assertSame(1, PlanOverageCharge::query()->where('office_id', 5)->count());
    }

    public function test_pending_total_sums_the_period(): void
    {
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $this->overage->recordDriverOverage(5, 100, 1500, 'USD');

        $this->assertSame(2500, $this->overage->pendingTotalMinor(5));
        $this->assertSame(0, $this->overage->pendingTotalMinor(6), 'another office is not affected');
    }

    public function test_ride_overage_is_a_noop_in_commission_region(): void
    {
        // No shard active → commission region → PlanUsageService returns null →
        // no ride overage is charged.
        $this->assertNull($this->overage->recordRideOverage(5, 7001));
        $this->assertSame(0, PlanOverageCharge::query()->count());
    }

    public function test_ride_within_limit_is_not_charged(): void
    {
        // 9 rides already this month, limit 10 → this settling ride is the 10th,
        // still within the plan → no overage.
        $overage = new PlanOverageService($this->usageStub(9, 10, 200));

        $this->assertNull($overage->recordRideOverage(5, 7100));
        $this->assertSame(0, PlanOverageCharge::query()->count());
    }

    public function test_ride_over_limit_is_charged_once(): void
    {
        // 10 rides already at a limit of 10 → this settling ride is the 11th →
        // overage. Idempotent per booking.
        $overage = new PlanOverageService($this->usageStub(10, 10, 200));

        $first = $overage->recordRideOverage(5, 7200);
        $second = $overage->recordRideOverage(5, 7200);

        $this->assertNotNull($first);
        $this->assertSame(200, (int) $first->amount_minor);
        $this->assertNull($second, 'the same ride must not be charged twice');
        $this->assertSame(1, PlanOverageCharge::query()->where('type', 'ride')->count());
    }

    public function test_closeout_invoices_pending_charges_and_is_idempotent(): void
    {
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $this->overage->recordDriverOverage(5, 100, 1500, 'USD');
        $period = now()->format('Y-m');

        $invoice = $this->overage->closeOffice(5, $period);

        $this->assertNotNull($invoice);
        $this->assertSame(2500, $invoice['total_minor']);
        $this->assertSame(2, $invoice['count']);
        $this->assertSame("OVR-{$period}-5", $invoice['invoice_ref']);

        // Charges are now invoiced → no longer pending, and re-closing bills nothing.
        $this->assertSame(0, $this->overage->pendingTotalMinor(5, $period));
        $this->assertNull($this->overage->closeOffice(5, $period));
        $this->assertSame(2, PlanOverageCharge::query()->where('status', 'invoiced')->count());
    }

    public function test_closeout_returns_null_when_nothing_pending(): void
    {
        $this->assertNull($this->overage->closeOffice(5));
    }

    public function test_close_period_bills_each_office_once(): void
    {
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $this->overage->recordDriverOverage(6, 200, 3000, 'USD');

        $invoices = $this->overage->closePeriod();

        $this->assertCount(2, $invoices);
        $this->assertSame([], $this->overage->closePeriod(), 're-closing bills nothing');
    }

    public function test_mark_collected_finalizes_an_invoice(): void
    {
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $period = now()->format('Y-m');
        $invoice = $this->overage->closeOffice(5, $period);

        $count = $this->overage->markCollected($invoice['invoice_ref']);

        $this->assertSame(1, $count);
        $this->assertSame(0, $this->overage->markCollected($invoice['invoice_ref']), 'collecting twice is a no-op');
        $this->assertSame(1, PlanOverageCharge::query()->where('status', 'collected')->count());
    }

    public function test_stripe_billed_overage_is_collected_at_the_next_paid_invoice(): void
    {
        // Stripe items ride on the office's UPCOMING invoice, so the next
        // invoice.paid means the already-handed-off overage is now paid.
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $ref = $this->overage->closeOffice(5)['invoice_ref'];
        PlanOverageCharge::query()->where('invoice_ref', $ref)->update(['collection_method' => 'stripe']);

        $collected = $this->overage->markStripeCollectedForOffice(5);

        $this->assertSame(1, $collected);
        $this->assertCount(1, $this->overage->invoices('collected'));
        $this->assertSame(0, $this->overage->markStripeCollectedForOffice(5), 'collecting twice is a no-op');
    }

    public function test_manual_overage_is_never_auto_collected(): void
    {
        $this->overage->recordDriverOverage(6, 99, 1000, 'USD');
        $this->overage->closeOffice(6);

        $this->assertSame(0, $this->overage->markStripeCollectedForOffice(6), 'manual invoices wait for a human to confirm the money');
        $this->assertCount(1, $this->overage->invoices('invoiced'));
    }

    public function test_invoices_listing_groups_by_ref_and_filters_status(): void
    {
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $this->overage->recordDriverOverage(5, 100, 1500, 'USD');
        $ref = $this->overage->closeOffice(5)['invoice_ref'];

        $all = $this->overage->invoices();
        $this->assertCount(1, $all);
        $this->assertSame(2500, $all[0]['total_minor']);
        $this->assertSame(2, $all[0]['items']);
        $this->assertSame($ref, $all[0]['invoice_ref']);

        $this->assertCount(1, $this->overage->invoices('invoiced'));
        $this->assertCount(0, $this->overage->invoices('collected'));

        $this->overage->markCollected($ref);
        $this->assertCount(0, $this->overage->invoices('invoiced'));
        $this->assertCount(1, $this->overage->invoices('collected'));
    }

    public function test_close_elapsed_closes_prior_periods_only(): void
    {
        // A charge in a prior period + one in the current month; only the prior
        // one should be closed at renewal.
        PlanOverageCharge::query()->create([
            'office_id' => 5, 'period' => '2000-01', 'type' => 'driver',
            'reference_type' => 'driver', 'reference_id' => 1, 'amount_minor' => 500,
            'currency_code' => 'USD', 'status' => 'pending', 'created_at' => now(),
        ]);
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');

        $invoices = $this->overage->closeElapsedForOffice(5);

        $this->assertCount(1, $invoices);
        $this->assertSame('2000-01', $invoices[0]['period']);
        $this->assertSame(1000, $this->overage->pendingTotalMinor(5), 'this month still accrues');
    }

    public function test_export_rows_flatten_a_closed_invoice(): void
    {
        $this->overage->recordDriverOverage(5, 99, 1000, 'USD');
        $this->overage->recordDriverOverage(5, 100, 1500, 'USD');
        $ref = $this->overage->closeOffice(5)['invoice_ref'];

        $rows = $this->overage->exportRows();

        $this->assertCount(1, $rows);
        $this->assertSame($ref, $rows[0][0]);
        $this->assertSame(5, $rows[0][1]);
        $this->assertSame('25.00', $rows[0][3]);
        $this->assertSame('USD', $rows[0][4]);
        $this->assertSame(2, $rows[0][5]);
        $this->assertSame('manual', $rows[0][6]);
        $this->assertSame('invoiced', $rows[0][8]);
    }

    public function test_closeout_announces_an_overage_invoiced_event(): void
    {
        $overage = new PlanOverageService(new PlanUsageService(), null, new EventBus());
        $overage->recordDriverOverage(5, 99, 1000, 'USD');

        $overage->closeOffice(5);

        $event = EventOutbox::query()->where('type', EventType::OVERAGE_INVOICED)->first();

        $this->assertNotNull($event);
        $this->assertContains('office.5', $event->channels);
        $this->assertContains('admin', $event->channels);
        $this->assertSame(5, $event->payload['office_id']);
        $this->assertSame(1000, $event->payload['total_minor']);
    }

    public function test_closeout_hands_off_to_the_billing_gateway(): void
    {
        OfficeSubscription::query()->create([
            'office_id' => 5, 'plan_key' => 'pro', 'status' => 'active',
            'fleet_commission_rate' => 0, 'office_commission_rate' => 0,
            'price_minor' => 0, 'currency_code' => 'USD',
            'provider_customer_id' => 'cus_5', 'provider_subscription_id' => 'sub_5',
        ]);

        $gateway = new class implements OverageBillingGateway {
            public array $seen = [];

            public function bill(array $invoice, ?string $customerId, ?string $subscriptionId): array
            {
                $this->seen = [$invoice['invoice_ref'], $customerId, $subscriptionId];

                return ['billed' => true, 'method' => 'stripe', 'external_ref' => 'ii_777', 'reason' => null];
            }
        };

        $overage = new PlanOverageService(new PlanUsageService(), $gateway);
        $overage->recordDriverOverage(5, 99, 1000, 'USD');

        $invoice = $overage->closeOffice(5);

        $this->assertSame('stripe', $invoice['collection_method']);
        $this->assertSame('ii_777', $invoice['external_ref']);
        $this->assertSame(['OVR-' . now()->format('Y-m') . '-5', 'cus_5', 'sub_5'], $gateway->seen);
        $this->assertSame('ii_777', PlanOverageCharge::query()->where('office_id', 5)->value('external_ref'));
        $this->assertSame('stripe', PlanOverageCharge::query()->where('office_id', 5)->value('collection_method'));
    }

    private function usageStub(int $ridesUsed, int $rideLimit, int $extraRideMinor): PlanUsageService
    {
        return new class($ridesUsed, $rideLimit, $extraRideMinor) extends PlanUsageService {
            public function __construct(private int $ridesUsed, private int $rideLimit, private int $extraRideMinor)
            {
            }

            public function forOffice(int $officeId): ?array
            {
                return [
                    'ride_limit' => $this->rideLimit,
                    'rides_used' => $this->ridesUsed,
                    'extra_ride_minor' => $this->extraRideMinor,
                    'currency' => 'USD',
                ];
            }
        };
    }
}
