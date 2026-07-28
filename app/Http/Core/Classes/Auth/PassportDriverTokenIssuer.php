<?php

namespace App\Http\Core\Classes\Auth;

use App\Http\Core\Const\Auth\TokenAudience;
use App\Models\Driver;

class PassportDriverTokenIssuer implements DriverTokenIssuer
{
    public function issue(Driver $driver, string $name): string
    {
        // Stamp the audience — see TokenAudience. Without it this token would
        // also authenticate on the user guard whenever a rider shares the id.
        return $driver->createToken($name, [TokenAudience::DRIVER])->accessToken;
    }

    public function revokeCurrent(Driver $driver): void
    {
        $token = $driver->token();

        if ($token !== null) {
            $token->revoke();
        }
    }
}
