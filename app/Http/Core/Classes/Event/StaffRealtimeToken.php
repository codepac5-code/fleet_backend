<?php

namespace App\Http\Core\Classes\Event;

use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Short-lived, shard-bound token that lets a panel staff session (which is
 * cookie/web-guard based, not Passport) authenticate to the realtime gateway.
 *
 * The gateway treats every client the same — it forwards `auth.token` as a
 * Bearer header to POST /realtime/authorize. Apps send a Passport token; the
 * panel sends one of these instead. Verified server-side in the authorize
 * endpoint, so the panel never gains any capability the token doesn't encode.
 *
 * The shard is baked in and re-checked on verify: an office token minted for
 * `sy` can never be replayed against another country's shard to read its rooms.
 */
class StaffRealtimeToken
{
    private const TTL_SECONDS = 3600;

    public static function mint(string $type, int $id, string $shard): string
    {
        return Crypt::encryptString(json_encode([
            't' => $type,
            'i' => $id,
            's' => strtolower($shard),
            'e' => time() + self::TTL_SECONDS,
        ]));
    }

    /**
     * Returns ['type' => ..., 'id' => ..., 'shard' => ...] for a valid,
     * unexpired token, or null. Only 'office' and 'admin' identities are ever
     * issued here — an app-facing type can never come out of this path.
     */
    public static function verify(string $token): ?array
    {
        try {
            $data = json_decode(Crypt::decryptString($token), true);
        } catch (Throwable $e) {
            return null;
        }

        if (! is_array($data)) {
            return null;
        }

        if ((int) ($data['e'] ?? 0) < time()) {
            return null;
        }

        $type = (string) ($data['t'] ?? '');

        if (! in_array($type, ['office', 'admin'], true)) {
            return null;
        }

        return [
            'type' => $type,
            'id' => (int) ($data['i'] ?? 0),
            'shard' => (string) ($data['s'] ?? ''),
        ];
    }
}
