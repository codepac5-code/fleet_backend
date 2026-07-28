<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Rider phone login, as it is actually shaped today (routes/user.php).
 *
 * The endpoint pair moved from "phone + code keyed on the phone number" to a
 * CHALLENGE handshake, which changes every contract in this file:
 *
 *   POST user/auth/otp/request  {dialCode, phone}       -> {challengeId, expiresIn, isNewUser}
 *   POST user/auth/otp/verify   {challengeId, code}     -> {accessToken, refreshToken, user}
 *   GET  user/me                                        -> UserPresenter (camelCase)
 *
 * Consequences worth pinning, because each one silently broke an old assertion:
 *  - the code is cached under `rider:challenge:{challengeId}`, NOT under the
 *    phone. Two requests for the SAME phone therefore produce two independent,
 *    simultaneously-valid challenges.
 *  - `isNewUser` is answered at REQUEST time (before the user is provisioned),
 *    not at verify time — verify always returns a session for an existing row.
 *  - an unknown/expired challenge is 410 `challenge_expired`; only a known
 *    challenge with a bad code is 422 `invalid_code`. The distinction is the
 *    whole reason both statuses exist.
 */
class RiderAuthTest extends FleetTestCase
{
    protected array $globalMigrations = [
        // UserPresenter reads rider_profiles for email/locale, and currencies
        // for the wallet currency block.
        '2026_07_11_000007_create_rider_account_tables.php',
        '2026_06_19_000002_create_currencies_table.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_23_085910_create_users_table.php',
        '2026_07_16_000001_create_rider_refresh_tokens_table.php',
        // UserPresenter counts open tickets + favourite offices on every present().
        '2026_07_11_000006_create_rider_support_tables.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
    ];

    private string $dialCode = '+974';
    private string $phone = '55123456';
    private string $full = '+97455123456';

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::flush();

        $this->app->bind(TokenIssuer::class, fn () => new class implements TokenIssuer {
            public function issue(User $user, string $name): string
            {
                return 'test-token-' . $user->id;
            }

            public function revokeCurrent(User $user): void
            {
            }
        });
    }

    /** The challenge id for a fresh OTP request against $this->phone. */
    private function requestOtp(?string $phone = null): string
    {
        return $this->postJson('user/auth/otp/request', [
            'dialCode' => $this->dialCode,
            'phone' => $phone ?? $this->phone,
        ])->assertStatus(200)->json('data.challengeId');
    }

    private function codeFor(string $challengeId): string
    {
        return (string) Cache::get('rider:challenge:' . $challengeId)['code'];
    }

    // ── request ─────────────────────────────────────────────────────────────

    public function test_request_otp_stores_a_challenge(): void
    {
        $res = $this->postJson('user/auth/otp/request', ['dialCode' => $this->dialCode, 'phone' => $this->phone])
            ->assertStatus(200)
            ->assertJsonPath('data.expiresIn', 300)
            ->assertJsonPath('data.isNewUser', true);

        $challengeId = $res->json('data.challengeId');
        $this->assertStringStartsWith('chg_', $challengeId);

        $cached = Cache::get('rider:challenge:' . $challengeId);
        $this->assertNotNull($cached);
        $this->assertSame($this->full, $cached['phone']);
        $this->assertFalse($cached['verified']);
    }

    public function test_request_otp_rejects_bad_phone(): void
    {
        // Fewer than 8 digits once dialCode+phone are concatenated => unusable.
        $this->postJson('user/auth/otp/request', ['dialCode' => '+9', 'phone' => '12'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_phone');
    }

    public function test_request_otp_requires_dial_code(): void
    {
        $this->postJson('user/auth/otp/request', ['phone' => $this->phone])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    /**
     * Resend is NOT throttled. The old contract answered 429 `otp_throttled` on
     * an immediate second request; ChallengeOtpLogic::request has no rate limit
     * and no route-level `throttle` middleware guards it (routes/user.php), so
     * every call mints a brand-new challenge and both stay valid for 5 minutes.
     *
     * Pinned as-is rather than asserted away: if throttling is reintroduced this
     * test must fail loudly and be rewritten, not quietly keep passing.
     */
    public function test_resend_mints_an_independent_second_challenge(): void
    {
        $first = $this->requestOtp();
        $second = $this->requestOtp();

        $this->assertNotSame($first, $second);
        $this->assertNotNull(Cache::get('rider:challenge:' . $first));
        $this->assertNotNull(Cache::get('rider:challenge:' . $second));
    }

    // ── verify ──────────────────────────────────────────────────────────────

    public function test_verify_without_request_is_expired(): void
    {
        $this->postJson('user/auth/otp/verify', ['challengeId' => 'chg_neverissued', 'code' => '1234'])
            ->assertStatus(410)
            ->assertJsonPath('error.code', 'challenge_expired');
    }

    public function test_verify_wrong_code_is_invalid(): void
    {
        $challengeId = $this->requestOtp();
        $code = $this->codeFor($challengeId);
        $wrong = $code === '0000' ? '1111' : '0000';

        $this->postJson('user/auth/otp/verify', ['challengeId' => $challengeId, 'code' => $wrong])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_code');
    }

    /**
     * Challenge scoping: a code is only valid for the challenge that minted it.
     * Two live challenges for two different phones must not be interchangeable,
     * or possession of any one SMS would unlock any other pending login.
     */
    public function test_code_from_another_challenge_is_rejected(): void
    {
        $mine = $this->requestOtp('55123456');
        $theirs = $this->requestOtp('55999999');

        $theirCode = $this->codeFor($theirs);
        $myCode = $this->codeFor($mine);

        // Guard against the 1-in-9000 collision that would make this vacuous.
        if ($theirCode === $myCode) {
            Cache::put('rider:challenge:' . $theirs, array_merge(
                Cache::get('rider:challenge:' . $theirs),
                ['code' => $myCode === '0000' ? '1111' : '0000']
            ), now()->addMinutes(5));
            $theirCode = $this->codeFor($theirs);
        }

        $this->postJson('user/auth/otp/verify', ['challengeId' => $mine, 'code' => $theirCode])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_code');
    }

    public function test_verify_creates_user_and_issues_token(): void
    {
        $challengeId = $this->requestOtp();

        $this->postJson('user/auth/otp/verify', ['challengeId' => $challengeId, 'code' => $this->codeFor($challengeId)])
            ->assertStatus(200)
            ->assertJsonPath('data.tokenType', 'Bearer')
            ->assertJsonPath('data.expiresIn', 3600)
            ->assertJsonPath('data.user.phoneNumber', $this->full)
            ->assertJsonPath('data.user.dialCode', $this->dialCode)
            ->assertJsonStructure(['data' => ['accessToken', 'refreshToken', 'user' => ['id']]]);

        $this->assertNotNull(User::query()->where('phoneNumber', $this->full)->first());
    }

    /**
     * `isNewUser` is decided when the OTP is REQUESTED (the row does not exist
     * yet at that point), so an already-registered phone reports false there —
     * the verify response carries no such flag at all.
     */
    public function test_existing_user_is_not_new_at_request_time(): void
    {
        User::query()->create([
            'firstName' => 'A', 'lastName' => 'B', 'phoneNumber' => $this->full,
            'dialCode' => $this->dialCode, 'password' => 'x', 'isActive' => 1,
        ]);

        $this->postJson('user/auth/otp/request', ['dialCode' => $this->dialCode, 'phone' => $this->phone])
            ->assertStatus(200)
            ->assertJsonPath('data.isNewUser', false);
    }

    /** Verifying an existing phone reuses the row instead of provisioning a second. */
    public function test_verify_reuses_the_existing_user_row(): void
    {
        $existing = User::query()->create([
            'firstName' => 'A', 'lastName' => 'B', 'phoneNumber' => $this->full,
            'dialCode' => $this->dialCode, 'password' => 'x', 'isActive' => 1,
        ]);

        $challengeId = $this->requestOtp();

        $this->postJson('user/auth/otp/verify', ['challengeId' => $challengeId, 'code' => $this->codeFor($challengeId)])
            ->assertStatus(200)
            ->assertJsonPath('data.user.id', (int) $existing->id);

        $this->assertSame(1, User::query()->where('phoneNumber', $this->full)->count());
    }

    // ── social ──────────────────────────────────────────────────────────────

    /** No Google/Apple credentials are bound, so AppServiceProvider wires UnconfiguredSocialVerifier. */
    public function test_social_unconfigured_is_422(): void
    {
        $this->postJson('user/auth/social', ['provider' => 'google', 'token' => 'tok'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'social_unavailable');
    }

    public function test_social_rejects_unknown_provider(): void
    {
        $this->postJson('user/auth/social', ['provider' => 'facebook', 'token' => 'tok'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    // ── session ─────────────────────────────────────────────────────────────

    public function test_me_requires_auth(): void
    {
        $this->getJson('user/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_me_returns_profile(): void
    {
        $user = User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => $this->full,
            'dialCode' => $this->dialCode, 'password' => 'x', 'isActive' => 1,
        ]);

        $this->actingAs($user, 'user')
            ->getJson('user/me')
            ->assertStatus(200)
            ->assertJsonPath('data.id', (int) $user->id)
            ->assertJsonPath('data.firstName', 'Test')
            ->assertJsonPath('data.lastName', 'Rider')
            ->assertJsonPath('data.isActive', true);
    }

    public function test_logout_is_204(): void
    {
        $user = new User();
        $user->id = 7;

        $this->actingAs($user, 'user')
            ->postJson('user/auth/logout')
            ->assertStatus(204);
    }
}
