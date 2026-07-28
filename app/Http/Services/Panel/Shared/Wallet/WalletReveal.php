<?php

namespace App\Http\Services\Panel\Shared\Wallet;

class WalletReveal
{
    public const KEY = 'panel_wallet_revealed_at';
    public const TTL = 300;

    public static function isRevealed(): bool
    {
        return self::secondsLeft() > 0;
    }

    public static function secondsLeft(): int
    {
        $at = session(self::KEY);

        if (! $at) {
            return 0;
        }

        $left = self::TTL - (time() - (int) $at);

        if ($left <= 0) {
            self::hide();

            return 0;
        }

        return $left;
    }

    public static function reveal(): void
    {
        session([self::KEY => time()]);
    }

    public static function hide(): void
    {
        session()->forget(self::KEY);
    }
}
