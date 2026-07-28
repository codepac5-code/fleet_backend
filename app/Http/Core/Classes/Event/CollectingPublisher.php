<?php

namespace App\Http\Core\Classes\Event;

class CollectingPublisher implements EventPublisher
{
    public array $sent = [];

    public function publish(string $channel, string $type, array $payload): void
    {
        $this->sent[] = ['channel' => $channel, 'type' => $type, 'payload' => $payload];
    }

    public function count(): int
    {
        return count($this->sent);
    }
}
