<?php

namespace App\Http\Core\Classes\Event;

use App\Http\Core\Const\Event\EventStatus;
use App\Models\EventOutbox;
use Throwable;

class OutboxRelay
{
    public function __construct(private EventPublisher $publisher)
    {
    }

    public function publishPending(int $limit = 100, int $maxAttempts = 5, int $backoffSeconds = 10): array
    {
        $rows = EventOutbox::query()
            ->where('status', EventStatus::PENDING)
            ->where(function ($q) {
                $q->whereNull('available_at')->orWhere('available_at', '<=', now());
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $published = 0;
        $failed = 0;
        $retried = 0;

        foreach ($rows as $row) {
            try {
                $payload = array_merge($row->payload ?? [], ['_event_uuid' => $row->uuid]);

                foreach ($row->channels as $channel) {
                    $this->publisher->publish($channel, $row->type, $payload);
                }

                $row->status = EventStatus::PUBLISHED;
                $row->published_at = now();
                $row->save();
                $published++;
            } catch (Throwable $e) {
                $row->attempts = (int) $row->attempts + 1;

                if ($row->attempts >= $maxAttempts) {
                    $row->status = EventStatus::FAILED;
                    $failed++;
                } else {
                    $row->available_at = now()->addSeconds($backoffSeconds * $row->attempts);
                    $retried++;
                }

                $row->save();
            }
        }

        return ['published' => $published, 'failed' => $failed, 'retried' => $retried];
    }
}
