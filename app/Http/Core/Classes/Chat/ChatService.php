<?php

namespace App\Http\Core\Classes\Chat;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\Channel;
use App\Http\Core\Const\Event\EventType;
use App\Http\Core\Repositories\Chat\ChatRepository;
use App\Models\ChatConversation;
use App\Models\ChatMessage;

class ChatService
{
    public function __construct(
        private ChatRepository $repository,
        private ?EventBus $events = null
    ) {
    }

    public function startOrGet(int $userId, int $officeId, ?int $bookingId = null): ChatConversation
    {
        return $this->repository->firstOrCreateConversation(
            ['user_id' => $userId, 'office_id' => $officeId, 'booking_id' => $bookingId],
            ['last_message_at' => null]
        );
    }

    public function send(int $conversationId, string $senderType, int $senderId, string $body): ChatMessage
    {
        $conversation = $this->repository->getConversation($conversationId);

        $message = $this->repository->createMessage([
            'conversation_id' => $conversationId,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'body' => $body,
            'read_at' => null,
            'created_at' => now(),
        ]);

        $conversation->last_message_at = now();
        $this->repository->saveConversation($conversation);

        if ($this->events) {
            $channel = $this->recipientChannel($conversation, $senderType);

            if ($channel !== null) {
                $this->events->emit(new DomainEvent(
                    EventType::CHAT_MESSAGE_CREATED,
                    [$channel],
                    [
                        'conversation_id' => $conversationId,
                        'message_id' => (int) $message->id,
                        'sender_type' => $senderType,
                        'sender_role' => $senderType,
                        'sender_id' => $senderId,
                        'body' => $body,
                        'preview' => mb_substr($body, 0, 120),
                        'created_at' => optional($message->created_at)->toIso8601ZuluString(),
                    ]
                ));
            }
        }

        return $message;
    }

    public function messages(int $conversationId, int $limit = 30, ?int $beforeId = null): array
    {
        return $this->repository->messages($conversationId, $limit, $beforeId);
    }

    public function conversationsForUser(int $userId): array
    {
        return $this->repository->conversationsForUser($userId);
    }

    public function conversationsForOffice(int $officeId): array
    {
        return $this->repository->conversationsForOffice($officeId);
    }

    public function markRead(int $conversationId, string $readerType): int
    {
        return $this->repository->markReadFrom($conversationId, $readerType);
    }

    private function recipientChannel(ChatConversation $conversation, string $senderType): ?string
    {
        if ($senderType === 'user') {
            return Channel::office((int) $conversation->office_id);
        }

        if ($senderType === 'office') {
            return Channel::user((int) $conversation->user_id);
        }

        return null;
    }
}
