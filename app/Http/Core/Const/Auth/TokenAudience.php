<?php

namespace App\Http\Core\Const\Auth;

/**
 * Which app a Passport access token was issued to.
 *
 * Passport only enforces a token's provider when the ISSUING CLIENT declares
 * one (`TokenGuard`: `$client->provider && $client->provider !== …`). This
 * install has a single personal-access client with a NULL provider, so that
 * check is skipped and every guard simply looks the token's `sub` up in its own
 * table — making a driver token and the same-id user's token interchangeable.
 *
 * So the audience is carried as a token SCOPE instead, stamped at issue time
 * and asserted by the `token-audience` middleware on each route group.
 */
final class TokenAudience
{
    public const USER = 'user';
    public const DRIVER = 'driver';

    /** @return array<string,string> scope => description, for Passport::tokensCan(). */
    public static function all(): array
    {
        return [
            self::USER => 'Act as a rider',
            self::DRIVER => 'Act as a driver',
        ];
    }
}
