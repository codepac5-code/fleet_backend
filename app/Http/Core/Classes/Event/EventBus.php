<?php

namespace App\Http\Core\Classes\Event;

use App\Http\Core\Const\Event\EventStatus;
use App\Models\EventOutbox;

class EventBus
{
    public function emit(DomainEvent $event): EventOutbox
    {
        return EventOutbox::query()->create([
            'uuid' => $event->uuid,
            'type' => $event->type,
            'channels' => $event->channels,
            'payload' => $event->payload,
            'status' => EventStatus::PENDING,
            'attempts' => 0,
            'available_at' => now(),
        ]);
    }
}
