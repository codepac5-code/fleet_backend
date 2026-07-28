<?php

namespace App\Http\Services\User\Auth\Logic;

use App\Models\RiderRefreshToken;
use Illuminate\Support\Str;

class RefreshTokenService
{
    private const TTL_DAYS = 60;

    public function issue(int $userId): string
    {
        $plain = Str::random(64);

        RiderRefreshToken::query()->create([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $plain),
            'expires_at' => now()->addDays(self::TTL_DAYS),
        ]);

        return $plain;
    }

    public function consume(string $refreshToken): ?int
    {
        $record = RiderRefreshToken::query()
            ->where('token_hash', hash('sha256', $refreshToken))
            ->where('expires_at', '>', now())
            ->first();

        if ($record === null) {
            return null;
        }

        $userId = (int) $record->user_id;
        $record->delete();

        return $userId;
    }

    public function revokeAllForUser(int $userId): void
    {
        RiderRefreshToken::query()->where('user_id', $userId)->delete();
    }
}
