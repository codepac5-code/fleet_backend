<?php

namespace Tests\Feature\Fleet;

/**
 * Subscription (recurring billing) webhook: `POST webhooks/subscriptions/{provider}`.
 *
 * The route is registered inline in bootstrap/app.php under the `webhooks`
 * prefix with only the `api` middleware — no auth, no tenant shard. It is
 * publicly reachable, and its entire security model is the Stripe signature
 * check in StripeSubscriptionWebhookGateway::verifyAndNormalize. Before this
 * route existed, a successful Stripe Checkout never created an OfficeSubscription
 * (the activation lives only in this webhook), so the whole self-signup → trial
 * flow dead-ended.
 *
 * This file exercises the parts that resolve BEFORE the shard is activated: the
 * signature boundary and provider routing. The apply/shard-activation happy path
 * is covered by SubscriptionWebhookTest (service level) — it cannot be driven
 * through HTTP here because ShardManager::activate repoints the tenant
 * connection to a real node, which the in-memory test harness has no equivalent
 * for. The controller answers with App\Http\Api\V1\Support\ApiResponse (bare
 * `{data:…}` / `{error:{code,…}}`), matching the payment webhook.
 */
class SubscriptionWebhookHttpTest extends FleetTestCase
{
    public function test_unknown_provider_returns_404(): void
    {
        $this->postJson('webhooks/subscriptions/bogus', ['type' => 'invoice.paid'])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    /**
     * Public endpoint: an UNSIGNED stripe delivery must be refused. The gateway
     * returns null when either the configured secret or the Stripe-Signature
     * header is absent, which the controller maps to 400 invalid_signature.
     */
    public function test_stripe_webhook_without_a_signature_is_rejected(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        $this->postJson('webhooks/subscriptions/stripe', ['type' => 'invoice.paid'])
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'invalid_signature');
    }

    public function test_stripe_webhook_with_a_forged_signature_is_rejected(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        $this->postJson(
            'webhooks/subscriptions/stripe',
            ['type' => 'invoice.paid'],
            ['Stripe-Signature' => 't=1,v1=deadbeef']
        )
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'invalid_signature');
    }
}
