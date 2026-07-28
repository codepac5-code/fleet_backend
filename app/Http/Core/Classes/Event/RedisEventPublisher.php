<?php

namespace App\Http\Core\Classes\Event;

use Illuminate\Support\Facades\Redis;

class RedisEventPublisher implements EventPublisher
{
    public const PREFIX = 'rt:';

    public function __construct(private string $connection = 'pubsub')
    {
    }

    public function publish(string $channel, string $type, array $payload): void
    {
        Redis::connection($this->connection)->publish(self::PREFIX . $channel, json_encode(self::message($type, $payload)));
    }

    public static function message(string $type, array $payload): array
    {
        return [
            'event' => $type,
            'data' => self::stripInternal($payload),
            'socket' => false,
        ];
    }

    private static function stripInternal(array $payload): array
    {
        return array_filter($payload, fn ($key) => !str_starts_with((string) $key, '_'), ARRAY_FILTER_USE_KEY);
    }
}
