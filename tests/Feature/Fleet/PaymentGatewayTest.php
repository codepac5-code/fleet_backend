<?php

namespace Tests\Feature\Fleet;

use App\Http\Api\V1\Support\ApiResponse;
use App\Http\Core\Classes\Payment\Gateway\GatewayRegistry;
use App\Http\Core\Classes\Payment\Gateway\GenericGateway;
use App\Http\Core\Classes\Payment\Gateway\StripeGateway;
use Illuminate\Http\Request;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    public function test_registry_maps_providers(): void
    {
        $registry = new GatewayRegistry();

        $this->assertInstanceOf(StripeGateway::class, $registry->for('stripe'));
        $this->assertInstanceOf(GenericGateway::class, $registry->for('syriatel'));
        $this->assertInstanceOf(GenericGateway::class, $registry->for('mtn'));
        $this->assertInstanceOf(GenericGateway::class, $registry->for('manual'));
        $this->assertNull($registry->for('unknown'));
    }

    public function test_generic_gateway_normalizes_body(): void
    {
        $request = Request::create('/x', 'POST', [
            'idempotency_key' => 'k1',
            'status' => 'succeeded',
            'provider_ref' => 'ref_1',
        ]);

        $normalized = (new GenericGateway())->verifyAndNormalize($request);

        $this->assertTrue($normalized['handled']);
        $this->assertSame('k1', $normalized['idempotency_key']);
        $this->assertSame('succeeded', $normalized['status']);
        $this->assertSame('ref_1', $normalized['provider_ref']);
    }

    public function test_stripe_gateway_rejects_unsigned_request(): void
    {
        $request = Request::create('/x', 'POST', [], [], [], [], '{}');

        $this->assertNull((new StripeGateway())->verifyAndNormalize($request));
    }

    public function test_api_response_envelope_shapes(): void
    {
        $ok = ApiResponse::data(['a' => 1], ['has_more' => false], 201);
        $this->assertSame(201, $ok->getStatusCode());
        $this->assertSame(['data' => ['a' => 1], 'meta' => ['has_more' => false]], $ok->getData(true));

        $err = ApiResponse::error('validation_failed', 'bad', ['field' => 'x'], 422);
        $this->assertSame(422, $err->getStatusCode());
        $this->assertSame(['error' => ['code' => 'validation_failed', 'message' => 'bad', 'details' => ['field' => 'x']]], $err->getData(true));
    }
}
