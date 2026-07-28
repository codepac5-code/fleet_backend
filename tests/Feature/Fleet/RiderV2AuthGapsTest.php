<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Auth\SocialVerifier;
use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Models\RiderProfile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RiderV2AuthGapsTest extends FleetTestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::flush();

        $this->app->bind(TokenIssuer::class, fn () => new class implements TokenIssuer {
            public function issue(User $user, string $name): string
            {
                return 'access-' . $user->id;
            }

            public function revokeCurrent(User $user): void
            {
            }
        });
    }

    private function user(string $phone = '+97455000000'): User
    {
        return User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => $phone,
            'dialCode' => '+974', 'password' => 'x', 'isActive' => 1,
        ]);
    }

    private function fakeSocial(array $profile): void
    {
        $this->app->bind(SocialVerifier::class, fn () => new class($profile) implements SocialVerifier {
            public function __construct(private array $profile)
            {
            }

            public function verify(string $provider, string $idToken): array
            {
                return $this->profile;
            }
        });
    }

    public function test_phone_change_requests_and_confirms(): void
    {
        $user = $this->user();

        $res = $this->actingAs($user, 'user')->postJson('user/auth/phone/change', ['dialCode' => '+974', 'phone' => '55123456'])
            ->assertStatus(200)
            ->assertJsonPath('data.isNewUser', false);

        $challengeId = $res->json('data.challengeId');
        $this->assertStringStartsWith('chg_', $challengeId);

        $code = (string) Cache::get('rider:phone-change:' . $challengeId)['code'];

        $this->actingAs($user, 'user')->postJson('user/auth/phone/change/verify', ['challengeId' => $challengeId, 'code' => $code])
            ->assertStatus(200)
            ->assertJsonPath('data.phoneNumber', '+97455123456');

        $this->assertSame('+97455123456', $user->fresh()->phoneNumber);
    }

    public function test_phone_change_rejects_taken_number(): void
    {
        $this->user('+97455123456');
        $me = $this->user('+97455000000');

        $this->actingAs($me, 'user')->postJson('user/auth/phone/change', ['dialCode' => '+974', 'phone' => '55123456'])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'phone_taken');
    }

    public function test_phone_change_verify_wrong_code(): void
    {
        $user = $this->user();

        $challengeId = $this->actingAs($user, 'user')->postJson('user/auth/phone/change', ['dialCode' => '+974', 'phone' => '55123456'])
            ->json('data.challengeId');

        $code = (string) Cache::get('rider:phone-change:' . $challengeId)['code'];
        $wrong = $code === '0000' ? '1111' : '0000';

        $this->actingAs($user, 'user')->postJson('user/auth/phone/change/verify', ['challengeId' => $challengeId, 'code' => $wrong])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'invalid_code');
    }

    public function test_social_unconfigured_is_unavailable(): void
    {
        $this->postJson('user/auth/social', ['provider' => 'google', 'token' => 'tok'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'social_unavailable');
    }

    public function test_social_login_creates_user_and_session(): void
    {
        $this->fakeSocial([
            'phone' => '+97455999888', 'first_name' => 'Soc', 'last_name' => 'Ial', 'email' => 'soc@x.com',
        ]);

        $this->postJson('user/auth/social', ['provider' => 'google', 'token' => 'tok'])
            ->assertStatus(200)
            ->assertJsonPath('data.tokenType', 'Bearer')
            ->assertJsonPath('data.isNewUser', true)
            ->assertJsonPath('data.user.phoneNumber', '+97455999888')
            ->assertJsonStructure(['data' => ['accessToken', 'refreshToken', 'expiresIn', 'user' => ['id']]]);

        $user = User::query()->where('phoneNumber', '+97455999888')->first();
        $this->assertNotNull($user);
        $this->assertSame('soc@x.com', RiderProfile::query()->where('user_id', $user->id)->value('email'));
    }

    public function test_social_rejects_bad_provider(): void
    {
        $this->postJson('user/auth/social', ['provider' => 'myspace', 'token' => 'tok'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }
}
