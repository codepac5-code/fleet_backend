<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Subscription\Billing\ManualOverageBillingGateway;
use App\Http\Core\Classes\Subscription\Billing\StripeInvoiceItemClient;
use App\Http\Core\Classes\Subscription\Billing\StripeOverageBillingGateway;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class OverageBillingGatewayTest extends TestCase
{
    private array $invoice = [
        'invoice_ref' => 'OVR-2026-07-5',
        'office_id' => 5,
        'period' => '2026-07',
        'total_minor' => 2500,
        'currency' => 'USD',
    ];

    public function test_manual_gateway_never_bills(): void
    {
        $result = (new ManualOverageBillingGateway())->bill($this->invoice, 'cus_1', 'sub_1');

        $this->assertFalse($result['billed']);
        $this->assertSame('manual', $result['method']);
        $this->assertNull($result['external_ref']);
    }

    public function test_stripe_gateway_creates_an_invoice_item(): void
    {
        $captured = [];
        $client = $this->fakeClient(function (...$args) use (&$captured) {
            $captured = $args;

            return 'ii_123';
        });

        $result = (new StripeOverageBillingGateway($client))->bill($this->invoice, 'cus_42', 'sub_42');

        $this->assertTrue($result['billed']);
        $this->assertSame('stripe', $result['method']);
        $this->assertSame('ii_123', $result['external_ref']);
        $this->assertSame(['cus_42', 'sub_42', 2500, 'USD'], array_slice($captured, 0, 4));
    }

    public function test_stripe_gateway_falls_back_to_manual_without_a_customer(): void
    {
        $result = (new StripeOverageBillingGateway($this->fakeClient(fn () => 'never')))
            ->bill($this->invoice, null, null);

        $this->assertFalse($result['billed']);
        $this->assertSame('manual', $result['method']);
        $this->assertSame('no_stripe_customer', $result['reason']);
    }

    public function test_stripe_gateway_degrades_to_manual_on_sdk_error(): void
    {
        $result = (new StripeOverageBillingGateway($this->fakeClient(function () {
            throw new RuntimeException('stripe down');
        })))->bill($this->invoice, 'cus_42', 'sub_42');

        $this->assertFalse($result['billed']);
        $this->assertSame('manual', $result['method']);
        $this->assertSame('stripe_error', $result['reason']);
    }

    private function fakeClient(callable $fn): StripeInvoiceItemClient
    {
        return new class($fn) implements StripeInvoiceItemClient {
            public function __construct(private $fn)
            {
            }

            public function createInvoiceItem(string $customerId, ?string $subscriptionId, int $amountMinor, string $currency, string $description): string
            {
                return ($this->fn)($customerId, $subscriptionId, $amountMinor, $currency, $description);
            }
        };
    }
}
