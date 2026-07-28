<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Chat\ChatService;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Repositories\Chat\EloquentChatRepository;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Models\EventOutbox;

class ChatTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2026_06_25_000007_create_event_outbox_table.php',
        '2026_06_25_000014_create_chat_conversations_table.php',
        '2026_06_25_000015_create_chat_messages_table.php',
    ];

    private ChatService $chat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chat = new ChatService(new EloquentChatRepository(), new EventBus());
    }

    public function test_start_is_idempotent(): void
    {
        $a = $this->chat->startOrGet(7, 3, 5001);
        $b = $this->chat->startOrGet(7, 3, 5001);

        $this->assertSame($a->id, $b->id);
    }

    public function test_messages_are_ordered_and_thread_both_ways(): void
    {
        $conversation = $this->chat->startOrGet(7, 3);

        $this->chat->send($conversation->id, 'user', 7, 'Hello office');
        $this->chat->send($conversation->id, 'office', 3, 'Hello rider');
        $this->chat->send($conversation->id, 'user', 7, 'Where is my ride?');

        $messages = $this->chat->messages($conversation->id);

        $this->assertCount(3, $messages);
        $this->assertSame('Hello office', $messages[0]->body);
        $this->assertSame('Where is my ride?', $messages[2]->body);
    }

    public function test_send_from_user_emits_event_to_office_channel(): void
    {
        $conversation = $this->chat->startOrGet(7, 3);
        $this->chat->send($conversation->id, 'user', 7, 'Hi');

        $event = EventOutbox::query()->where('type', EventType::CHAT_MESSAGE_CREATED)->first();

        $this->assertNotNull($event);
        $this->assertContains(Channel::office(3), $event->channels);
        $this->assertNotContains(Channel::user(7), $event->channels);
    }

    public function test_office_sees_its_conversations_ordered_by_recency(): void
    {
        $a = $this->chat->startOrGet(7, 3);
        $b = $this->chat->startOrGet(8, 3);
        $this->chat->startOrGet(9, 4);

        $this->chat->send($a->id, 'user', 7, 'first');
        $this->chat->send($b->id, 'user', 8, 'later');

        $conversations = $this->chat->conversationsForOffice(3);

        $this->assertCount(2, $conversations);
        $this->assertSame($b->id, $conversations[0]->id);
    }

    public function test_mark_read_only_marks_counterparty_messages(): void
    {
        $conversation = $this->chat->startOrGet(7, 3);
        $this->chat->send($conversation->id, 'user', 7, 'm1');
        $this->chat->send($conversation->id, 'office', 3, 'm2');
        $this->chat->send($conversation->id, 'office', 3, 'm3');

        $marked = $this->chat->markRead($conversation->id, 'user');

        $this->assertSame(2, $marked);
    }
}
