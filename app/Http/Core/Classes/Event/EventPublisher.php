<?php

namespace App\Http\Core\Classes\Event;

interface EventPublisher
{
    public function publish(string $channel, string $type, array $payload): void;
}
