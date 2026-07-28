<?php

namespace Tests\Feature\Fleet;

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

class NotificationTemplatesTest extends FleetTestCase
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
    private OutboxRelay $relay;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bus = new EventBus();
        $notifications = new NotificationService(new TemplateRenderer(), new CollectingPushSender());
        $this->relay = new OutboxRelay(new EventNotificationPublisher($notifications));
    }

    private function notif(string $type, int $id)
    {
        return AppNotification::query()->where('notifiable_type', $type)->where('notifiable_id', $id)->first();
    }

    public function test_offer_created_notifies_driver(): void
    {
        $this->bus->emit(new DomainEvent(
            EventType::DISPATCH_OFFER_CREATED,
            [Channel::driver(101)],
            ['booking_id' => 7001, 'office_id' => 3, 'distance_m' => 300]
        ));
        $this->relay->publishPending();

        $this->assertSame('ride_offer_driver', $this->notif('driver', 101)->template_key);
        $this->assertStringContainsString('#7001', $this->notif('driver', 101)->body);
    }

    public function test_ride_released_notifies_driver_and_office_not_booking(): void
    {
        $this->bus->emit(new DomainEvent(
            EventType::RIDE_RELEASED,
            [Channel::booking(5001), Channel::driver(9), Channel::office(3)],
            ['booking_id' => 5001, 'driver_id' => 9, 'office_id' => 3, 'total_minor' => 4950]
        ));
        $this->relay->publishPending();

        $this->assertStringContainsString('#5001', $this->notif('driver', 9)->body);
        $this->assertNotNull($this->notif('office', 3));
        $this->assertNull($this->notif('booking', 5001));
    }

    public function test_chat_message_notifies_office_in_arabic(): void
    {
        $this->bus->emit(new DomainEvent(
            EventType::CHAT_MESSAGE_CREATED,
            [Channel::office(3)],
            ['conversation_id' => 1, 'body' => 'مرحبا', '_locale' => 'ar']
        ));
        $this->relay->publishPending();

        $this->assertStringContainsString('مرحبا', $this->notif('office', 3)->body);
    }

    public function test_payout_notifies_owner(): void
    {
        $this->bus->emit(new DomainEvent(
            EventType::WALLET_PAYOUT,
            [Channel::driver(9)],
            ['payout_id' => 1, 'amount' => 5000]
        ));
        $this->relay->publishPending();

        $this->assertSame('payout_paid', $this->notif('driver', 9)->template_key);
    }

    public function test_rating_notifies_ratee(): void
    {
        $this->bus->emit(new DomainEvent(
            EventType::RATING_RECEIVED,
            [Channel::driver(9)],
            ['booking_id' => 5001, 'stars' => 5]
        ));
        $this->relay->publishPending();

        $this->assertStringContainsString('5', $this->notif('driver', 9)->body);
    }
}
