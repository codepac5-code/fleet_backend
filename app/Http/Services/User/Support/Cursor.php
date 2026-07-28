<?php

namespace App\Http\Services\User\Support;

class Cursor
{
    public static function encode(?int $id): ?string
    {
        return $id === null ? null : base64_encode(json_encode(['id' => $id]));
    }

    public static function decode(?string $cursor): ?int
    {
        if ($cursor === null || $cursor === '') {
            return null;
        }

        $decoded = json_decode((string) base64_decode($cursor, true), true);

        return is_array($decoded) && isset($decoded['id']) ? (int) $decoded['id'] : null;
    }

    public static function limit($value, int $default = 20, int $max = 50): int
    {
        $limit = (int) ($value ?: $default);

        return max(1, min($max, $limit));
    }
}
