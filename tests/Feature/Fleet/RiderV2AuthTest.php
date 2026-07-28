<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RiderV2AuthTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000007_create_rider_account_tables.php',
        '2026_06_19_000002_create_currencies_table.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_23_085910_create_users_table.php',
        '2026_07_16_000001_create_rider_refresh_tokens_table.php',
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
                return 'access-' . $user->id . '-' . uniqid();
            }

            public function revokeCurrent(User $user): void
            {
            }
        });
    }

    private function challengeCode(string $challengeId): string
    {
        return (string) Cache::get('rider:challenge:' . $challengeId)['code'];
    }

    private function requestOtp(): string
    {
        $res = $this->postJson('user/auth/otp/request', [
            'dialCode' => $this->dialCode,
            'phone' => $this->phone,
        ])->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.isNewUser', true);

        return $res->json('data.challengeId');
    }

    public function test_request_returns_challenge(): void
    {
        $challengeId = $this->requestOtp();

        $this->assertStringStartsWith('chg_', $challengeId);
        $this->assertNotNull(Cache::get('rider:challenge:' . $challengeId));
    }

    public function test_request_rejects_bad_phone(): void
    {
        $this->postJson('user/auth/otp/request', ['dialCode' => '+9', 'phone' => '12'])
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error.code', 'invalid_phone');
    }

    public function test_verify_wrong_code_is_invalid(): void
    {
        $challengeId = $this->requestOtp();
        $code = $this->challengeCode($challengeId);
        $wrong = $code === '0000' ? '1111' : '0000';

        $this->postJson('user/auth/otp/verify', ['challengeId' => $challengeId, 'code' => $wrong])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_code');
    }

    public function test_verify_creates_user_and_issues_session(): void
    {
        $challengeId = $this->requestOtp();
        $code = $this->challengeCode($challengeId);

        $this->postJson('user/auth/otp/verify', ['challengeId' => $challengeId, 'code' => $code])
            ->assertStatus(200)
            ->assertJsonPath('data.tokenType', 'Bearer')
            ->assertJsonPath('data.user.phoneNumber', $this->full)
            ->assertJsonPath('data.user.dialCode', $this->dialCode)
            ->assertJsonPath('meta', null)
            ->assertJsonStructure(['data' => ['accessToken', 'refreshToken', 'expiresIn', 'user' => ['id', 'walletBalance', 'locale']]]);

        $this->assertNotNull(User::query()->where('phoneNumber', $this->full)->first());
    }

    public function test_register_requires_verified_challenge(): void
    {
        $challengeId = $this->requestOtp();

        $this->postJson('user/auth/register', [
            'challengeId' => $challengeId,
            'firstName' => 'Ahmed',
            'lastName' => 'Ali',
            'email' => 'rider@example.com',
        ])->assertStatus(422)->assertJsonPath('error.code', 'challenge_not_verified');
    }

    public function test_register_after_verify_sets_profile(): void
    {
        $challengeId = $this->requestOtp();
        $code = $this->challengeCode($challengeId);
        $this->postJson('user/auth/otp/verify', ['challengeId' => $challengeId, 'code' => $code])->assertStatus(200);

        $this->postJson('user/auth/register', [
            'challengeId' => $challengeId,
            'firstName' => 'Ahmed',
            'lastName' => 'Ali',
            'email' => 'rider@example.com',
            'country' => 'QA',
        ])->assertStatus(201)
            ->assertJsonPath('data.user.firstName', 'Ahmed')
            ->assertJsonPath('data.user.lastName', 'Ali')
            ->assertJsonPath('data.user.email', 'rider@example.com');
    }

    public function test_refresh_rotates_tokens(): void
    {
        $challengeId = $this->requestOtp();
        $code = $this->challengeCode($challengeId);
        $refreshToken = $this->postJson('user/auth/otp/verify', ['challengeId' => $challengeId, 'code' => $code])
            ->json('data.refreshToken');

        $this->postJson('user/auth/refresh', ['refreshToken' => $refreshToken])
            ->assertStatus(200)
            ->assertJsonPath('data.tokenType', 'Bearer')
            ->assertJsonStructure(['data' => ['accessToken', 'refreshToken', 'expiresIn']]);

        $this->postJson('user/auth/refresh', ['refreshToken' => $refreshToken])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'invalid_refresh_token');
    }

    public function test_logout_returns_204(): void
    {
        $user = User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => $this->full,
            'dialCode' => $this->dialCode, 'password' => 'x', 'isActive' => 1,
        ]);

        $this->actingAs($user, 'user')->postJson('user/auth/logout')->assertStatus(204);
    }

    public function test_me_requires_auth(): void
    {
        $this->getJson('user/me')->assertStatus(401)->assertJsonPath('error.code', 'unauthenticated');
    }

    public function test_me_returns_camel_case_user(): void
    {
        $user = User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => $this->full,
            'dialCode' => $this->dialCode, 'password' => 'x', 'isActive' => 1,
        ]);

        $this->actingAs($user, 'user')
            ->getJson('user/me')
            ->assertStatus(200)
            ->assertJsonPath('data.firstName', 'Test')
            ->assertJsonPath('data.isActive', true);
    }
}
