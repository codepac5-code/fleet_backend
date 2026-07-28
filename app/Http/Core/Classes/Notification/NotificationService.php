<?php

namespace App\Http\Core\Classes\Notification;

use App\Http\Core\Classes\Event\DomainEvent;
use App\Http\Core\Classes\Event\EventBus;
use App\Http\Core\Const\Event\EventType;
use App\Models\AppNotification;
use App\Models\DeviceToken;

class NotificationService
{
    public function __construct(
        private TemplateRenderer $renderer,
        private PushSender $push,
        private ?MailSender $mail = null,
        private ?EventBus $events = null
    ) {
    }

    public function registerDevice(string $ownerType, int $ownerId, string $token, ?string $platform = null): DeviceToken
    {
        return DeviceToken::query()->updateOrCreate(
            ['token' => $token],
            ['owner_type' => $ownerType, 'owner_id' => $ownerId, 'platform' => $platform, 'last_seen_at' => now()]
        );
    }

    public function send(string $notifiableType, int $notifiableId, string $templateKey, string $type, string $locale, array $vars, ?string $eventUuid = null): AppNotification
    {
        $rendered = $this->renderer->render($templateKey, $locale, $vars);

        $attributes = [
            'template_key' => $templateKey,
            'type' => $type,
            'locale' => $locale,
            'title' => $rendered['subject'],
            'body' => $rendered['body'],
            'data' => $this->stripInternal($vars),
        ];

        if ($eventUuid !== null) {
            $notification = AppNotification::query()->firstOrCreate(
                ['event_uuid' => $eventUuid, 'notifiable_type' => $notifiableType, 'notifiable_id' => $notifiableId],
                $attributes
            );
        } else {
            $notification = AppNotification::query()->create(array_merge($attributes, [
                'event_uuid' => null,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
            ]));
        }

        if ($notification->wasRecentlyCreated) {
            $this->emitCreated($notification, $notifiableType, $notifiableId);

            if (in_array('push', $rendered['channels'], true)) {
                $this->pushToDevices($notifiableType, $notifiableId, $rendered, $attributes['data']);
            }

            if ($this->mail !== null && in_array('email', $rendered['channels'], true) && !empty($vars['_email'])) {
                $this->mail->send(
                    (string) $vars['_email'],
                    (string) ($rendered['subject'] ?? ''),
                    (string) $rendered['body'],
                    $attributes['data']
                );
            }
        }

        return $notification;
    }

    public function markRead(int $notificationId): void
    {
        AppNotification::query()->where('id', $notificationId)->whereNull('read_at')->update(['read_at' => now()]);
    }

    private function emitCreated(AppNotification $notification, string $notifiableType, int $notifiableId): void
    {
        if ($this->events === null) {
            return;
        }

        $unread = AppNotification::query()
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('read_at')
            ->count();

        $this->events->emit(new DomainEvent(
            EventType::NOTIFICATION_CREATED,
            [$notifiableType . '.' . $notifiableId],
            [
                'id' => (int) $notification->id,
                'type' => $notification->type,
                'template_key' => $notification->template_key,
                'title' => $notification->title,
                'body' => $notification->body,
                'data' => $notification->data,
                'read_at' => null,
                'unread_count' => (int) $unread,
            ]
        ));
    }

    private function pushToDevices(string $ownerType, int $ownerId, array $rendered, array $data): void
    {
        $tokens = DeviceToken::query()
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->pluck('token');

        foreach ($tokens as $token) {
            $this->push->send($token, (string) ($rendered['subject'] ?? ''), (string) $rendered['body'], $data);
        }
    }

    private function stripInternal(array $vars): array
    {
        return array_filter($vars, fn ($k) => !str_starts_with((string) $k, '_'), ARRAY_FILTER_USE_KEY);
    }
}
