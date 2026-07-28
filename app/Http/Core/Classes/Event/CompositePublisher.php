<?php

namespace App\Http\Core\Classes\Event;

class CompositePublisher implements EventPublisher
{
    private array $publishers;

    public function __construct(EventPublisher ...$publishers)
    {
        $this->publishers = $publishers;
    }

    public function publish(string $channel, string $type, array $payload): void
    {
        foreach ($this->publishers as $publisher) {
            $publisher->publish($channel, $type, $payload);
        }
    }
}
