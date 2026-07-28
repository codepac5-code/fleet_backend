<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\CollectingPublisher;
use App\Http\Core\Classes\Event\CompositePublisher;
use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Classes\Event\OutboxRelay;
use App\Http\Core\Classes\Notification\CollectingPushSender;
use App\Http\Core\Classes\Notification\EventNotificationPublisher;
use App\Http\Core\Classes\Notification\NotificationService;
use App\Http\Core\Classes\Notification\TemplateRenderer;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Models\AppNotification;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\DB;

class NotificationTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_25_000008_create_notification_templates_table.php',
    ];

    protected array $tenantMigrations = [
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000009_create_app_notifications_table.php',
        '2026_06_25_000010_create_app_device_tokens_table.php',
    ];

    private EventBus $bus;
    private NotificationService $notifications;
    private CollectingPushSender $push;
    private CollectingPublisher $socket;
    private OutboxRelay $relay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = new EventBus();
        $this->push = new CollectingPushSender();
        $this->notifications = new NotificationService(new TemplateRenderer(), $this->push);
        $this->socket = new CollectingPublisher();
        $this->relay = new OutboxRelay(new CompositePublisher($this->socket, new EventNotificationPublisher($this->notifications)));
    }

    public function test_event_fans_to_socket_and_notification_with_push(): void
    {
        $this->notifications->registerDevice('driver', 9, 'tok-a', 'android');
        $this->notifications->registerDevice('driver', 9, 'tok-b', 'ios');

        $this->bus->emit(new DomainEvent(
            EventType::DISPATCH_RIDE_ASSIGNED,
            [Channel::booking(7001), Channel::driver(9), Channel::office(3)],
            ['booking_id' => 7001, 'driver_id' => 9, 'office_id' => 3]
        ));
        $this->relay->publishPending();

        $this->assertSame(3, $this->socket->count());
        $notif = AppNotification::query()->where('notifiable_type', 'driver')->where('notifiable_id', 9)->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('#7001', $notif->body);
        $this->assertSame(2, $this->push->count());
        $this->assertSame(0, (int) AppNotification::query()->whereIn('notifiable_type', ['office', 'booking'])->count());
    }

    public function test_relay_does_not_duplicate_notifications(): void
    {
        $this->bus->emit(new DomainEvent(EventType::DISPATCH_RIDE_ASSIGNED, [Channel::driver(9)], ['booking_id' => 1]));
        $this->relay->publishPending();
        $this->relay->publishPending();
        $this->assertSame(1, (int) AppNotification::query()->where('notifiable_id', 9)->count());
    }

    public function test_locale_and_db_template_override(): void
    {
        $this->bus->emit(new DomainEvent(EventType::WALLET_CREDITED, [Channel::user(7)], ['amount' => 'USD 100.00', '_locale' => 'ar']));
        $this->relay->publishPending();
        $ar = AppNotification::query()->where('notifiable_id', 7)->first();
        $this->assertSame('ar', $ar->locale);
        $this->assertStringContainsString('محفظتك', $ar->body);

        $prev = DB::getDefaultConnection();
        DB::setDefaultConnection('global');
        NotificationTemplate::query()->create([
            'key' => 'wallet_credited', 'channels' => ['inapp'],
            'subject_i18n' => ['en' => 'x'], 'body_i18n' => ['en' => 'DB-OVERRIDE +{amount}'], 'is_active' => true,
        ]);
        DB::setDefaultConnection($prev);

        $this->bus->emit(new DomainEvent(EventType::WALLET_CREDITED, [Channel::user(8)], ['amount' => 'USD 5.00', '_locale' => 'en']));
        $this->relay->publishPending();
        $override = AppNotification::query()->where('notifiable_id', 8)->first();
        $this->assertStringContainsString('DB-OVERRIDE +USD 5.00', $override->body);
        $this->assertArrayNotHasKey('_event_uuid', $override->data ?? []);
    }
}
