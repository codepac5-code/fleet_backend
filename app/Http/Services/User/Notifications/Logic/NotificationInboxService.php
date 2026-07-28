<?php

namespace App\Http\Services\User\Notifications\Logic;

use App\Http\Core\Classes\Notification\NotificationService as CoreNotifications;
use App\Http\Core\Exceptions\DomainException;
use App\Http\Services\User\Support\Cursor;
use App\Models\AppNotification;
use App\Models\DeviceToken;

class NotificationInboxService
{
    private const OWNER = 'user';

    public function __construct(private CoreNotifications $core)
    {
    }

    public function list(int $userId, bool $unreadOnly, ?string $cursor, $limit): array
    {
        $limit = Cursor::limit($limit);
        $beforeId = Cursor::decode($cursor);

        $rows = AppNotification::query()
            ->where('notifiable_type', self::OWNER)
            ->where('notifiable_id', $userId)
            ->when($unreadOnly, fn ($q) => $q->whereNull('read_at'))
            ->when($beforeId !== null, fn ($q) => $q->where('id', '<', $beforeId))
            ->orderByDesc('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $rows->count() > $limit;
        $items = $rows->take($limit);

        return [
            'items' => $items->map(fn (AppNotification $n) => $this->present($n))->values()->all(),
            'unreadCount' => $this->unreadCount($userId),
            'nextCursor' => $hasMore ? Cursor::encode((int) $items->last()->id) : null,
        ];
    }

    public function read(int $userId, int $id): array
    {
        $notification = AppNotification::query()
            ->where('id', $id)
            ->where('notifiable_type', self::OWNER)
            ->where('notifiable_id', $userId)
            ->first();

        if ($notification === null) {
            throw DomainException::notFound();
        }

        $this->core->markRead($id);

        return $this->present($notification->fresh());
    }

    public function readAll(int $userId): array
    {
        $updated = AppNotification::query()
            ->where('notifiable_type', self::OWNER)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return ['updated' => (int) $updated];
    }

    public function registerDevice(int $userId, string $token, ?string $platform): array
    {
        $device = $this->core->registerDevice(self::OWNER, $userId, $token, $platform);

        return [
            'id' => (int) $device->id,
            'token' => $device->token,
            'platform' => $device->platform,
            'last_seen_at' => $device->last_seen_at !== null ? $device->last_seen_at->toIso8601ZuluString() : null,
        ];
    }

    public function unregisterDevice(int $userId, string $token): void
    {
        DeviceToken::query()
            ->where('owner_type', self::OWNER)
            ->where('owner_id', $userId)
            ->where('token', $token)
            ->delete();
    }

    private function unreadCount(int $userId): int
    {
        return (int) AppNotification::query()
            ->where('notifiable_type', self::OWNER)
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    private function present(AppNotification $n): array
    {
        return [
            'id' => (int) $n->id,
            'template_key' => $n->template_key,
            'type' => $n->type,
            'locale' => $n->locale,
            'title' => $n->title,
            'body' => $n->body,
            'data' => $n->data,
            'read_at' => $n->read_at !== null ? $n->read_at->toIso8601ZuluString() : null,
            'created_at' => $n->created_at !== null ? $n->created_at->toIso8601ZuluString() : null,
        ];
    }
}
