<?php

namespace App\Http\Core\Classes\Auth;

use App\Http\Core\Const\Auth\TokenAudience;
use App\Models\User;

class PassportTokenIssuer implements TokenIssuer
{
    public function issue(User $user, string $name): string
    {
        // Stamp the audience — see TokenAudience. Without it this token would
        // also authenticate on the driver guard whenever a driver shares the id.
        return $user->createToken($name, [TokenAudience::USER])->accessToken;
    }

    public function revokeCurrent(User $user): void
    {
        $token = $user->token();

        if ($token !== null) {
            $token->revoke();
        }
    }
}
