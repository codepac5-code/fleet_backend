<?php

namespace App\Http\Core\Classes\Notification;

use App\Http\Core\Classes\Event\EventPublisher;
use App\Http\Core\Const\Notification\NotificationEventMap;
use App\Http\Core\Const\Notification\TemplateCatalog;
use App\Models\Driver;
use App\Models\DriverAppSetting;
use App\Models\Office;
use App\Models\User;
use Throwable;

class EventNotificationPublisher implements EventPublisher
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function publish(string $channel, string $type, array $payload): void
    {
        // Channels are region-namespaced (`sy.driver.33`); drop the leading
        // region segment so the recipient type/id still resolve. Without this
        // every notification is silently dropped once a shard is active.
        $parts = explode('.', $channel);

        if (count($parts) === 3 && ! ctype_digit($parts[0])) {
            array_shift($parts);
        }

        if (count($parts) !== 2 || ! ctype_digit($parts[1])) {
            return;
        }

        [$recipientType, $recipientId] = $parts;

        $templateKey = NotificationEventMap::templateFor($type, $recipientType);

        if ($templateKey === null) {
            return;
        }

        // Inject the recipient's email so a template that opts into the `email`
        // channel can actually be delivered. NotificationService still only
        // emails when the template lists `email` AND this is non-empty, so
        // phone-only riders (no address) simply never get an email — additive
        // and safe. Empty for a missing address; never throws.
        if (empty($payload['_email'])) {
            $email = $this->recipientEmail($recipientType, (int) $recipientId);

            if ($email !== null) {
                $payload['_email'] = $email;
            }
        }

        $this->notifications->send(
            $recipientType,
            (int) $recipientId,
            $templateKey,
            $type,
            $this->recipientLocale($recipientType, (int) $recipientId, $payload),
            $payload,
            $payload['_event_uuid'] ?? null
        );
    }

    /**
     * The recipient's email address (rider/driver/office), or null. Wrapped so a
     * missing column or row never drops the notification. User is global;
     * driver/office resolve on the active shard, matching recipientLocale().
     */
    private function recipientEmail(string $type, int $id): ?string
    {
        try {
            $email = match ($type) {
                'user' => User::query()->whereKey($id)->value('email'),
                'driver' => Driver::query()->whereKey($id)->value('email'),
                'office' => Office::query()->whereKey($id)->value('email'),
                default => null,
            };

            return is_string($email) && trim($email) !== '' ? trim($email) : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Localise the notification to the RECIPIENT's saved UI language, not the
     * actor's request locale. A background push is rendered on the server long
     * after the triggering request, so the emitter's `_locale` is the wrong
     * language for the person receiving it. Falls back to the event locale, then
     * the catalog default, and never throws — a missing row must not drop the
     * notification.
     */
    private function recipientLocale(string $type, int $id, array $payload): string
    {
        try {
            $stored = match ($type) {
                'user' => User::query()->whereKey($id)->value('locale'),
                'driver' => DriverAppSetting::query()->where('driver_id', $id)->value('locale'),
                default => null,
            };

            if (is_string($stored) && in_array($stored, ['en', 'ar'], true)) {
                return $stored;
            }
        } catch (Throwable $e) {
            // fall through to the event/default locale
        }

        return $payload['_locale'] ?? TemplateCatalog::DEFAULT_LOCALE;
    }
}
