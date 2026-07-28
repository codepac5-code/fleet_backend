<?php

namespace Tests\Feature\Fleet;

use App\Models\AppNotification;
use App\Models\DeviceToken;
use App\Models\User;

class RiderV2NotificationsTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000009_create_app_notifications_table.php',
        '2026_06_25_000010_create_app_device_tokens_table.php',
    ];

    private function asUser(int $id = 7): self
    {
        $user = new User();
        $user->id = $id;

        return $this->actingAs($user, 'user');
    }

    private function notif(int $userId, ?string $readAt = null): AppNotification
    {
        return AppNotification::query()->create([
            'notifiable_type' => 'user', 'notifiable_id' => $userId,
            'template_key' => 'ride_completed', 'type' => 'trip', 'locale' => 'en',
            'title' => 'Trip completed', 'body' => 'Your fare was 24.00', 'data' => ['tripId' => 4100],
            'read_at' => $readAt,
        ]);
    }

    public function test_list_and_unread_count(): void
    {
        $this->notif(7);
        $this->notif(7, now()->toDateTimeString());
        $this->notif(9);

        $this->asUser()->getJson('user/notifications')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.unreadCount', 1)
            ->assertJsonPath('data.items.0.type', 'trip');

        $this->asUser()->getJson('user/notifications?unread=true')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.items');
    }

    public function test_mark_read_owner_only(): void
    {
        $n = $this->notif(7);

        $this->asUser()->postJson("user/notifications/{$n->id}/read")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $n->id);

        $this->assertNotNull($n->fresh()->read_at);

        $other = $this->notif(9);
        $this->asUser(7)->postJson("user/notifications/{$other->id}/read")->assertStatus(404);
    }

    public function test_read_all(): void
    {
        $this->notif(7);
        $this->notif(7);

        $this->asUser()->postJson('user/notifications/read-all')
            ->assertStatus(200)
            ->assertJsonPath('data.updated', 2);

        $this->assertSame(0, AppNotification::query()->where('notifiable_id', 7)->whereNull('read_at')->count());
    }

    public function test_register_and_unregister_device(): void
    {
        $this->asUser()->postJson('user/devices', ['token' => 'fcm-abc', 'platform' => 'ios'])
            ->assertStatus(201)
            ->assertJsonPath('data.token', 'fcm-abc')
            ->assertJsonPath('data.platform', 'ios');

        $this->assertTrue(DeviceToken::query()->where('owner_type', 'user')->where('owner_id', 7)->where('token', 'fcm-abc')->exists());

        $this->asUser()->deleteJson('user/devices/fcm-abc')->assertStatus(204);
        $this->assertFalse(DeviceToken::query()->where('token', 'fcm-abc')->exists());
    }

    public function test_register_device_validates_platform(): void
    {
        $this->asUser()->postJson('user/devices', ['token' => 'x', 'platform' => 'blackberry'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'validation_failed');
    }
}
