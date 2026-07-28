<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Auth\TokenIssuer;
use App\Models\User;

class RiderV2ProfileTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_07_11_000007_create_rider_account_tables.php',
        '2026_07_11_000003_create_saved_places_table.php',
        '2026_07_15_000001_add_rider_api_missing_columns.php',
        '2026_07_16_000002_add_rider_preferences_columns.php',
        '2026_06_19_000002_create_currencies_table.php',
    ];

    protected array $tenantMigrations = [
        '2024_10_23_085910_create_users_table.php',
        '2026_07_11_000006_create_rider_support_tables.php',
        '2026_06_25_000012_create_favorite_offices_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

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

    private function user(): User
    {
        return User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => '+97455123456',
            'dialCode' => '+974', 'password' => 'x', 'isActive' => 1,
        ]);
    }

    public function test_update_profile(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'user')->patchJson('user/me', [
            'firstName' => 'Ahmed',
            'lastName' => 'Ali',
            'email' => 'rider@example.com',
            'avatarUrl' => 'https://cdn.fleetapp.net/img/sample.png',
            'language' => 'ar',
        ])->assertStatus(200)
            ->assertJsonPath('data.firstName', 'Ahmed')
            ->assertJsonPath('data.lastName', 'Ali')
            ->assertJsonPath('data.email', 'rider@example.com')
            ->assertJsonPath('data.locale', 'ar')
            ->assertJsonPath('data.photo', 'https://cdn.fleetapp.net/img/sample.png');
    }

    public function test_delete_account_is_204_and_soft_deletes(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'user')->deleteJson('user/account')->assertStatus(204);

        $this->assertNotNull(User::withTrashed()->find($user->id)->deleted_at);
    }

    public function test_places_crud(): void
    {
        $user = $this->user();

        $id = $this->actingAs($user, 'user')->postJson('user/me/places', [
            'label' => 'Home', 'icon' => 'home', 'address' => 'West Bay, Doha', 'lat' => 25.2854, 'lng' => 51.531,
        ])->assertStatus(201)
            ->assertJsonPath('data.label', 'Home')
            ->assertJsonPath('data.title', 'Home')
            ->assertJsonPath('data.icon', 'home')
            ->json('data.id');

        $this->actingAs($user, 'user')->getJson('user/me/places')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $id);

        $this->actingAs($user, 'user')->patchJson("user/me/places/{$id}", [
            'label' => 'Work', 'lat' => 25.3, 'lng' => 51.5,
        ])->assertStatus(200)->assertJsonPath('data.label', 'Work');

        $this->actingAs($user, 'user')->deleteJson("user/me/places/{$id}")->assertStatus(204);

        $this->actingAs($user, 'user')->getJson('user/me/places')->assertJsonCount(0, 'data');
    }

    public function test_places_reject_missing_coords(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'user')->postJson('user/me/places', ['label' => 'Home'])
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('error.code', 'validation_failed');
    }

    public function test_safety_contacts_and_auto_share(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'user')->getJson('user/me/safety-contacts')
            ->assertStatus(200)
            ->assertJsonPath('data.autoShare', true)
            ->assertJsonCount(0, 'data.contacts');

        $id = $this->actingAs($user, 'user')->postJson('user/me/safety-contacts', [
            'name' => 'Mom', 'phone' => '+97450000000', 'relation' => 'parent', 'primary' => true,
        ])->assertStatus(201)
            ->assertJsonPath('data.is_primary', true)
            ->assertJsonPath('data.relation', 'parent')
            ->json('data.id');

        $this->actingAs($user, 'user')->patchJson('user/me/safety-contacts/auto-share', ['enabled' => false])
            ->assertStatus(200)
            ->assertJsonPath('data.enabled', false);

        $this->actingAs($user, 'user')->getJson('user/me/safety-contacts')
            ->assertJsonPath('data.autoShare', false)
            ->assertJsonCount(1, 'data.contacts');

        $this->actingAs($user, 'user')->deleteJson("user/me/safety-contacts/{$id}")->assertStatus(204);
    }

    public function test_notification_prefs(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'user')->getJson('user/me/notifications-prefs')
            ->assertStatus(200)
            ->assertJsonPath('data.tripUpdates', true)
            ->assertJsonPath('data.promotions', true);

        $this->actingAs($user, 'user')->patchJson('user/me/notifications-prefs', ['promotions' => false])
            ->assertStatus(200)
            ->assertJsonPath('data.promotions', false)
            ->assertJsonPath('data.tripUpdates', true);

        $this->actingAs($user, 'user')->getJson('user/me/notifications-prefs')
            ->assertJsonPath('data.promotions', false);
    }

    public function test_privacy_prefs(): void
    {
        $user = $this->user();

        $this->actingAs($user, 'user')->patchJson('user/me/privacy', ['marketing' => false])
            ->assertStatus(200)
            ->assertJsonPath('data.marketing', false)
            ->assertJsonPath('data.locationDuringTrips', true);
    }
}
