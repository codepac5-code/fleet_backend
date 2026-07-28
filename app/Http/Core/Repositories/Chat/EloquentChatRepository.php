<?php

namespace App\Http\Core\Repositories\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;

class EloquentChatRepository implements ChatRepository
{
    public function firstOrCreateConversation(array $keys, array $values): ChatConversation
    {
        return ChatConversation::query()->firstOrCreate($keys, $values);
    }

    public function getConversation(int $conversationId): ChatConversation
    {
        return ChatConversation::query()->findOrFail($conversationId);
    }

    public function ownedByUser(int $conversationId, int $userId): bool
    {
        return ChatConversation::query()->where('id', $conversationId)->where('user_id', $userId)->exists();
    }

    public function saveConversation(ChatConversation $conversation): void
    {
        $conversation->save();
    }

    public function createMessage(array $attributes): ChatMessage
    {
        return ChatMessage::query()->create($attributes);
    }

    public function messages(int $conversationId, int $limit, ?int $beforeId): array
    {
        $query = ChatMessage::query()
            ->where('conversation_id', $conversationId)
            ->orderByDesc('id');

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        return array_reverse($query->limit($limit)->get()->all());
    }

    public function conversationsForUser(int $userId): array
    {
        return ChatConversation::query()
            ->where('user_id', $userId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    public function conversationsForOffice(int $officeId): array
    {
        return ChatConversation::query()
            ->where('office_id', $officeId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    public function markReadFrom(int $conversationId, string $readerType): int
    {
        return ChatMessage::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_type', '!=', $readerType)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
