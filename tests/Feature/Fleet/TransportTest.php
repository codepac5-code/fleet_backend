<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\RedisEventPublisher;
use App\Http\Core\Classes\Notification\CollectingMailSender;
use App\Http\Core\Classes\Notification\CollectingPushSender;
use App\Http\Core\Classes\Notification\FcmPushSender;
use App\Http\Core\Classes\Notification\NotificationService;
use App\Http\Core\Classes\Notification\TemplateRenderer;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\DB;

class TransportTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_25_000008_create_notification_templates_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_25_000009_create_app_notifications_table.php',
        '2026_06_25_000010_create_app_device_tokens_table.php',
    ];

    public function test_redis_message_shape_strips_internal_keys(): void
    {
        $message = RedisEventPublisher::message('dispatch.ride_assigned', [
            'booking_id' => 5001,
            'driver_id' => 9,
            '_event_uuid' => 'abc',
            '_locale' => 'ar',
        ]);

        $this->assertSame('dispatch.ride_assigned', $message['event']);
        $this->assertFalse($message['socket']);
        $this->assertSame(['booking_id' => 5001, 'driver_id' => 9], $message['data']);
        $this->assertArrayNotHasKey('_event_uuid', $message['data']);
    }

    public function test_fcm_message_targets_token_and_stringifies_data(): void
    {
        $message = FcmPushSender::buildMessage('tok-1', 'Title', 'Body', [
            'booking_id' => 5001,
            '_event_uuid' => 'abc',
        ]);

        $this->assertSame('tok-1', $message['message']['token']);
        $this->assertSame('Title', $message['message']['notification']['title']);
        $this->assertSame('Body', $message['message']['notification']['body']);
        $this->assertSame('5001', $message['message']['data']['booking_id']);
        $this->assertArrayNotHasKey('_event_uuid', $message['message']['data']);
    }

    public function test_email_channel_sends_when_address_present(): void
    {
        $prev = DB::getDefaultConnection();
        DB::setDefaultConnection('global');
        NotificationTemplate::query()->create([
            'key' => 'wallet_credited', 'channels' => ['inapp', 'email'],
            'subject_i18n' => ['en' => 'Wallet topped up'],
            'body_i18n' => ['en' => 'Credited {amount}'],
            'is_active' => true,
        ]);
        DB::setDefaultConnection($prev);

        $mail = new CollectingMailSender();
        $service = new NotificationService(new TemplateRenderer(), new CollectingPushSender(), $mail);

        $service->send('user', 7, 'wallet_credited', 'wallet.credited', 'en', [
            'amount' => 'USD 5.00', '_email' => 'rider@example.com', '_event_uuid' => 'evt-1',
        ], 'evt-1');

        $this->assertSame(1, $mail->count());
        $this->assertSame('rider@example.com', $mail->sent[0]['to']);
        $this->assertSame('Wallet topped up', $mail->sent[0]['subject']);
        $this->assertStringContainsString('USD 5.00', $mail->sent[0]['body']);
        $this->assertArrayNotHasKey('_email', $mail->sent[0]['data']);
    }

    public function test_email_channel_skipped_without_address(): void
    {
        $prev = DB::getDefaultConnection();
        DB::setDefaultConnection('global');
        NotificationTemplate::query()->create([
            'key' => 'wallet_credited', 'channels' => ['email'],
            'subject_i18n' => ['en' => 'x'], 'body_i18n' => ['en' => 'y'], 'is_active' => true,
        ]);
        DB::setDefaultConnection($prev);

        $mail = new CollectingMailSender();
        $service = new NotificationService(new TemplateRenderer(), new CollectingPushSender(), $mail);

        $service->send('user', 8, 'wallet_credited', 'wallet.credited', 'en', ['amount' => 'USD 1.00'], 'evt-2');

        $this->assertSame(0, $mail->count());
    }
}
