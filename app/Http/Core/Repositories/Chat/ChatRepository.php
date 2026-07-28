<?php

namespace App\Http\Core\Repositories\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;

interface ChatRepository
{
    public function firstOrCreateConversation(array $keys, array $values): ChatConversation;

    public function getConversation(int $conversationId): ChatConversation;

    public function ownedByUser(int $conversationId, int $userId): bool;

    public function saveConversation(ChatConversation $conversation): void;

    public function createMessage(array $attributes): ChatMessage;

    public function messages(int $conversationId, int $limit, ?int $beforeId): array;

    public function conversationsForUser(int $userId): array;

    public function conversationsForOffice(int $officeId): array;

    public function markReadFrom(int $conversationId, string $readerType): int;
}
