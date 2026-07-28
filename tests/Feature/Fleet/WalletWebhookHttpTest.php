<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Ledger\FleetWalletService;
use App\Http\Core\Classes\Ledger\LedgerService;
use App\Http\Core\Classes\Payment\PaymentService;
use App\Models\LedgerPayment;

/**
 * Payment-gateway webhook: `POST webhooks/payments/{provider}`.
 *
 * This file used to target `user/payments/webhook/{provider}`, which does not
 * exist — every case 404'd, so the ONLY inbound money path in the system had no
 * working coverage at all.
 *
 * The route is registered inline in bootstrap/app.php under the `webhooks`
 * prefix with only the `api` middleware: no auth, no tenant shard. It is
 * therefore reachable by anyone on the internet, and its entire security model
 * is the per-gateway signature check in
 * PaymentGateway::verifyAndNormalize. That check is what most of this file
 * exercises.
 *
 * Note the envelope differs from the rider API: this controller answers with
 * App\Http\Api\V1\Support\ApiResponse — a bare `{data:…}` / `{error:{code,…}}`,
 * NOT the `Reply` envelope with status/statusCode.
 *
 * Provider routing (GatewayRegistry): `stripe` → StripeGateway (real HMAC
 * verification); `syriatel`/`mtn`/`manual` → GenericGateway (trusts the body);
 * anything else → 404.
 */
class WalletWebhookHttpTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_24_000001_create_ledger_accounts_table.php',
        '2026_06_24_000002_create_ledger_transactions_table.php',
        '2026_06_24_000003_create_ledger_entries_table.php',
        '2026_06_24_000004_create_commission_snapshots_table.php',
        '2026_06_25_000003_create_ledger_payments_table.php',
        // crediting the wallet emits a `wallet.credited` domain event through
        // the transactional outbox; without this table the whole credit path
        // rolls back as a 422 webhook_error.
        '2026_06_25_000007_create_event_outbox_table.php',
    ];

    private function wallet(): FleetWalletService
    {
        return new FleetWalletService(new LedgerService());
    }

    private function intent(int $userId, int $minor, string $key): void
    {
        (new PaymentService($this->wallet()))->createTopUpIntent($userId, $minor, 'USD', 'manual', $key);
    }

    // ── the credit path ─────────────────────────────────────────────────────

    public function test_manual_webhook_credits_wallet_end_to_end(): void
    {
        $this->intent(7, 10000, 'whk1');
        $this->assertSame(0, $this->wallet()->walletBalanceMinor('user', 7, 'USD'));

        $this->postJson('webhooks/payments/manual', [
            'idempotency_key' => 'whk1',
            'status' => 'succeeded',
            'provider_ref' => 'ref_1',
        ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'succeeded')
            ->assertJsonPath('data.uuid', fn ($v) => $v !== null);

        $this->assertSame(10000, $this->wallet()->walletBalanceMinor('user', 7, 'USD'));
    }

    /**
     * Gateways retry aggressively; a redelivered event must not credit twice.
     * This is the single most expensive bug this endpoint could have.
     */
    public function test_duplicate_webhook_delivery_credits_once(): void
    {
        $this->intent(8, 5000, 'whk2');

        $payload = ['idempotency_key' => 'whk2', 'status' => 'succeeded', 'provider_ref' => 'ref_2'];
        $this->postJson('webhooks/payments/manual', $payload)->assertStatus(200);
        $this->postJson('webhooks/payments/manual', $payload)->assertStatus(200);

        $this->assertSame(5000, $this->wallet()->walletBalanceMinor('user', 8, 'USD'));
    }

    /** Three deliveries, still one credit. */
    public function test_repeated_redelivery_still_credits_once(): void
    {
        $this->intent(11, 2500, 'whk-repeat');

        $payload = ['idempotency_key' => 'whk-repeat', 'status' => 'succeeded', 'provider_ref' => 'ref_r'];
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('webhooks/payments/manual', $payload)->assertStatus(200);
        }

        $this->assertSame(2500, $this->wallet()->walletBalanceMinor('user', 11, 'USD'));
    }

    /** A failed payment must not move any money. */
    public function test_failed_status_credits_nothing(): void
    {
        $this->intent(9, 7000, 'whk-fail');

        $this->postJson('webhooks/payments/manual', [
            'idempotency_key' => 'whk-fail',
            'status' => 'failed',
            'provider_ref' => 'ref_f',
        ])->assertStatus(200);

        $this->assertSame(0, $this->wallet()->walletBalanceMinor('user', 9, 'USD'));
    }

    // ── provider routing ────────────────────────────────────────────────────

    public function test_unknown_provider_returns_404(): void
    {
        $this->postJson('webhooks/payments/bogus', [
            'idempotency_key' => 'x',
            'status' => 'succeeded',
        ])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    /** The registry is case-insensitive, so `MANUAL` must route like `manual`. */
    public function test_provider_lookup_is_case_insensitive(): void
    {
        $this->intent(12, 1000, 'whk-case');

        $this->postJson('webhooks/payments/MANUAL', [
            'idempotency_key' => 'whk-case',
            'status' => 'succeeded',
        ])->assertStatus(200);

        $this->assertSame(1000, $this->wallet()->walletBalanceMinor('user', 12, 'USD'));
    }

    // ── signature verification (the only security boundary) ─────────────────

    /**
     * The endpoint is unauthenticated and publicly reachable, so an UNSIGNED
     * stripe delivery must be refused. StripeGateway::verifyAndNormalize
     * returns null when either the configured secret or the Stripe-Signature
     * header is absent, which the controller maps to 400 invalid_signature.
     *
     * Without this, anyone who learns a pending idempotency_key could credit an
     * arbitrary wallet by POSTing to this URL.
     */
    public function test_stripe_webhook_without_a_signature_is_rejected(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        $this->postJson('webhooks/payments/stripe', [
            'idempotency_key' => 'whk-unsigned',
            'status' => 'succeeded',
        ])
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'invalid_signature');
    }

    public function test_stripe_webhook_with_a_forged_signature_is_rejected(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);

        $this->postJson(
            'webhooks/payments/stripe',
            ['idempotency_key' => 'whk-forged', 'status' => 'succeeded'],
            ['Stripe-Signature' => 't=1,v1=deadbeef']
        )
            ->assertStatus(400)
            ->assertJsonPath('error.code', 'invalid_signature');
    }

    /** …and a forged delivery credits nothing. */
    public function test_a_rejected_stripe_delivery_moves_no_money(): void
    {
        config(['services.stripe.webhook_secret' => 'whsec_test']);
        $this->intent(13, 9000, 'whk-forged-2');

        $this->postJson(
            'webhooks/payments/stripe',
            ['idempotency_key' => 'whk-forged-2', 'status' => 'succeeded'],
            ['Stripe-Signature' => 't=1,v1=deadbeef']
        )->assertStatus(400);

        $this->assertSame(0, $this->wallet()->walletBalanceMinor('user', 13, 'USD'));
    }

    // ── validation ──────────────────────────────────────────────────────────

    public function test_missing_idempotency_key_is_422(): void
    {
        $this->postJson('webhooks/payments/manual', ['status' => 'succeeded'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_missing_status_is_422(): void
    {
        $this->postJson('webhooks/payments/manual', ['idempotency_key' => 'whk-nostatus'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /**
     * An event for a key that was never issued is a domain error (422), not a
     * crash and not a silent success — otherwise a typo'd key would look like
     * an accepted payment to the gateway and stop being retried.
     */
    public function test_unknown_idempotency_key_is_a_webhook_error(): void
    {
        $this->postJson('webhooks/payments/manual', [
            'idempotency_key' => 'never-issued',
            'status' => 'succeeded',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'webhook_error');

        $this->assertSame(0, LedgerPayment::query()->count());
    }
}
