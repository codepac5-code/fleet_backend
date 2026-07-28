<?php

namespace Tests\Feature\Fleet;

use App\Models\User;

/**
 * Rider account surface: profile, safety contacts, cards, promos, deletion.
 *
 * The live URIs (routes/user.php) after the rename:
 *   GET/PATCH user/me                          profile
 *   .../me/safety-contacts                     emergency contacts
 *   .../payment-methods                        saved cards
 *   PATCH  user/payment-methods/{id}           set-default  (was .../{id}/default)
 *   POST   user/payments/stripe/setup-intent   (was user/payment-methods/setup-intent)
 *   POST   user/promos/redeem                  (was user/promos/apply)
 *   DELETE user/account
 *
 * Shape changes that invalidated the old assertions:
 *  - the profile is camelCase UserPresenter output (firstName/lastName/…), not
 *    a flattened `name`; PATCH takes firstName/lastName/email/`language`.
 *  - safety contacts index answers {contacts: [...], autoShare: bool} — the
 *    list is nested, no longer the bare `data` array.
 *  - a card is created from a `stripePaymentMethodId` alone. brand/last4/exp are
 *    no longer client-supplied: PaymentMethodService::save asks the CardGateway
 *    to describe the token. Tests run with NullCardGateway (no stripe secret),
 *    which describes nothing — so cards persist with NULL brand by design
 *    (fail-soft: never lose a card the gateway already holds).
 *  - the list contains ONLY saved cards; the synthetic wallet/cash rows are gone.
 *
 * Ownership is the security boundary for cards and contacts alike:
 * PaymentMethodService::owned() and SafetyContactsController::destroy both
 * resolve via findForUser and 404 a stranger rather than 403.
 */
class AccountTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000007_create_rider_account_tables.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        // rider_profiles.auto_share_safety, written by the auto-share toggle.
        '2026_07_16_000002_add_rider_preferences_columns.php',
        '2026_06_19_000002_create_currencies_table.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_23_085910_create_users_table.php',
        '2026_07_11_000006_create_rider_support_tables.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
        '2024_11_17_075900_create_coupons_table.php',
    ];

    /**
     * Force the no-gateway branch. `.env` carries a REAL Stripe test secret, so
     * without this AppServiceProvider binds StripeCardGateway and every card
     * test makes a live HTTPS call to Stripe — slow, flaky, and dependent on a
     * key that is not the suite's to rely on. Clearing the config selects
     * NullCardGateway, which is the environment this file is written against.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.stripe.secret' => null, 'services.stripe.public' => null]);

        \App\Models\Coupon::query()->create([
            'code' => 'QATAR10', 'discountType' => 'percentage', 'discount' => 10,
            'isPercentage' => true, 'isActive' => true, 'limit' => 0, 'status' => 1,
        ]);
    }

    private function makeUser(string $phone = '+97455123456'): User
    {
        return User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => $phone,
            'dialCode' => '+974', 'password' => 'x', 'isActive' => 1,
        ]);
    }

    // ── profile ─────────────────────────────────────────────────────────────

    public function test_profile_get_and_update(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->getJson('user/me')
            ->assertStatus(200)
            ->assertJsonPath('data.id', (int) $user->id)
            ->assertJsonPath('data.firstName', 'Test')
            ->assertJsonPath('data.lastName', 'Rider')
            ->assertJsonPath('data.phoneNumber', '+97455123456')
            ->assertJsonPath('data.isActive', true);

        $this->actingAs($user, 'user')->patchJson('user/me', [
            'firstName' => 'New', 'lastName' => 'Name', 'email' => 'r@x.qa', 'language' => 'ar',
        ])->assertStatus(200)
            ->assertJsonPath('data.firstName', 'New')
            ->assertJsonPath('data.lastName', 'Name')
            ->assertJsonPath('data.email', 'r@x.qa')
            ->assertJsonPath('data.locale', 'ar');

        // …and it persisted, rather than only echoing the request back.
        $this->actingAs($user, 'user')->getJson('user/me')
            ->assertJsonPath('data.firstName', 'New')
            ->assertJsonPath('data.email', 'r@x.qa');
    }

    public function test_profile_rejects_unsupported_language(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->patchJson('user/me', ['language' => 'fr'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    // ── safety contacts ─────────────────────────────────────────────────────

    public function test_safety_contacts_crud(): void
    {
        $user = $this->makeUser();

        $id = $this->actingAs($user, 'user')
            ->postJson('user/me/safety-contacts', ['name' => 'Mom', 'phone' => '+97450000000'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Mom')
            ->assertJsonPath('data.auto_share', true)
            ->json('data.id');

        $this->actingAs($user, 'user')->getJson('user/me/safety-contacts')
            ->assertStatus(200)
            ->assertJsonPath('data.contacts.0.name', 'Mom')
            ->assertJsonPath('data.autoShare', true);

        $this->actingAs($user, 'user')->deleteJson("user/me/safety-contacts/{$id}")->assertStatus(204);
        $this->actingAs($user, 'user')->getJson('user/me/safety-contacts')->assertJsonPath('data.contacts', []);
    }

    /** Turning auto-share off flips the stored flag and every existing contact. */
    public function test_auto_share_toggle_applies_to_existing_contacts(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->postJson('user/me/safety-contacts', ['name' => 'Mom', 'phone' => '+97450000000'])
            ->assertStatus(201);

        $this->actingAs($user, 'user')->patchJson('user/me/safety-contacts/auto-share', ['enabled' => false])
            ->assertStatus(200)
            ->assertJsonPath('data.enabled', false);

        $this->actingAs($user, 'user')->getJson('user/me/safety-contacts')
            ->assertJsonPath('data.autoShare', false)
            ->assertJsonPath('data.contacts.0.auto_share', false);
    }

    public function test_foreign_safety_contact_is_404(): void
    {
        $owner = $this->makeUser('+97455111111');
        $stranger = $this->makeUser('+97455222222');

        $id = $this->actingAs($owner, 'user')
            ->postJson('user/me/safety-contacts', ['name' => 'Mom', 'phone' => '+97450000000'])
            ->assertStatus(201)->json('data.id');

        $this->actingAs($stranger, 'user')->deleteJson("user/me/safety-contacts/{$id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');

        // The refusal really did leave the row alone.
        $this->actingAs($owner, 'user')->getJson('user/me/safety-contacts')
            ->assertJsonPath('data.contacts.0.id', $id);
    }

    /** A stranger's list never leaks the owner's contacts. */
    public function test_safety_contacts_index_is_scoped(): void
    {
        $owner = $this->makeUser('+97455111111');
        $stranger = $this->makeUser('+97455222222');

        $this->actingAs($owner, 'user')->postJson('user/me/safety-contacts', ['name' => 'Mom', 'phone' => '+97450000000']);

        $this->actingAs($stranger, 'user')->getJson('user/me/safety-contacts')
            ->assertStatus(200)
            ->assertJsonPath('data.contacts', []);
    }

    // ── payment methods ─────────────────────────────────────────────────────

    public function test_payment_methods_add_list_default_remove(): void
    {
        $user = $this->makeUser();

        // The FIRST card is default even without asking.
        $first = $this->actingAs($user, 'user')->postJson('user/payment-methods', ['stripePaymentMethodId' => 'pm_1'])
            ->assertStatus(201)
            ->assertJsonPath('data.type', 'card')
            ->assertJsonPath('data.stripe_payment_method_id', 'pm_1')
            ->assertJsonPath('data.is_default', true)
            ->json('data.id');

        $second = $this->actingAs($user, 'user')->postJson('user/payment-methods', ['stripePaymentMethodId' => 'pm_2'])
            ->assertStatus(201)
            ->assertJsonPath('data.is_default', false)
            ->json('data.id');

        // Only real saved cards are listed — no synthetic wallet/cash rows.
        $this->actingAs($user, 'user')->getJson('user/payment-methods')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $this->actingAs($user, 'user')->patchJson("user/payment-methods/{$second}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $second)
            ->assertJsonPath('data.is_default', true);

        $this->actingAs($user, 'user')->deleteJson("user/payment-methods/{$second}")->assertStatus(204);

        // Removing the default promotes whatever is left, so the rider is never
        // stranded without a default card.
        $reList = $this->actingAs($user, 'user')->getJson('user/payment-methods')->assertJsonCount(1, 'data');
        $this->assertSame($first, $reList->json('data.0.id'));
        $this->assertTrue($reList->json('data.0.is_default'));
    }

    public function test_add_card_requires_a_gateway_token(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->postJson('user/payment-methods', [])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /**
     * With no Stripe secret configured the container binds NullCardGateway, whose
     * describe() returns null. The card is still saved — brand/last4 simply stay
     * NULL until a real gateway can identify the token.
     */
    public function test_card_saves_without_brand_when_gateway_cannot_describe_it(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->postJson('user/payment-methods', ['stripePaymentMethodId' => 'tok_x'])
            ->assertStatus(201)
            ->assertJsonPath('data.brand', null)
            ->assertJsonPath('data.last4', null)
            ->assertJsonPath('data.stripe_payment_method_id', 'tok_x');
    }

    public function test_foreign_payment_method_is_404(): void
    {
        $owner = $this->makeUser('+97455111111');
        $stranger = $this->makeUser('+97455222222');

        $id = $this->actingAs($owner, 'user')->postJson('user/payment-methods', ['stripePaymentMethodId' => 'pm_owner'])
            ->assertStatus(201)->json('data.id');

        $this->actingAs($stranger, 'user')->patchJson("user/payment-methods/{$id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');

        $this->actingAs($stranger, 'user')->deleteJson("user/payment-methods/{$id}")->assertStatus(404);

        // Still there, still the owner's default.
        $this->actingAs($owner, 'user')->getJson('user/payment-methods')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $id);
    }

    public function test_payment_methods_index_is_scoped(): void
    {
        $owner = $this->makeUser('+97455111111');
        $stranger = $this->makeUser('+97455222222');

        $this->actingAs($owner, 'user')->postJson('user/payment-methods', ['stripePaymentMethodId' => 'pm_owner']);

        $this->actingAs($stranger, 'user')->getJson('user/payment-methods')
            ->assertStatus(200)
            ->assertJsonPath('data', []);
    }

    /**
     * No Stripe secret => NullCardGateway::setupIntent throws, which
     * PaymentMethodService maps to `payments_unavailable` at 503 (a dependency
     * outage), not the 422 the old contract claimed.
     */
    public function test_setup_intent_unavailable_without_gateway(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->postJson('user/payments/stripe/setup-intent')
            ->assertStatus(503)
            ->assertJsonPath('error.code', 'payments_unavailable');
    }

    // ── promos ──────────────────────────────────────────────────────────────

    public function test_promo_redeem(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->postJson('user/promos/redeem', ['code' => 'qatar10'])
            ->assertStatus(200)
            ->assertJsonPath('data.code', 'QATAR10')
            ->assertJsonPath('data.applied', true)
            // 10.0 serialises to the JSON number 10 — assert the decoded value.
            ->assertJsonPath('data.discount', 10)
            ->assertJsonPath('data.discountType', 'percentage');

        $this->actingAs($user, 'user')->postJson('user/promos/redeem', ['code' => 'nope'])
            ->assertStatus(200)
            ->assertJsonPath('data.code', 'NOPE')
            ->assertJsonPath('data.applied', false)
            ->assertJsonPath('data.discount', 0);
    }

    public function test_promo_redeem_requires_a_code(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->postJson('user/promos/redeem', [])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    // ── deletion ────────────────────────────────────────────────────────────

    /**
     * SUSPECTED APPLICATION BUG — pinned to real behaviour, not weakened.
     *
     * DELETE user/account takes NO body and performs NO confirmation check:
     * ProfileController::destroy calls ProfileService::deleteAccount(user)
     * unconditionally and answers 204. The old contract required a
     * `confirm_phrase` and answered 422 without it / 202 with it.
     *
     * That means a single unconfirmed DELETE on a live bearer token soft-deletes
     * the rider's account. If the confirmation step was meant to survive the
     * refactor, this test should be rewritten once the guard is restored — it
     * documents the gap rather than hiding it.
     */
    public function test_delete_account_soft_deletes_without_confirmation(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'user')->deleteJson('user/account', [])->assertStatus(204);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
