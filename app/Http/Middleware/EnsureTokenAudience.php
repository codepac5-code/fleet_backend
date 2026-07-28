<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Assert the access token was issued to THIS app (`token-audience:driver|user`).
 *
 * Why this exists: Passport enforces a token's provider only when the issuing
 * client declares one, and this install's personal-access client has a NULL
 * provider. Every guard therefore accepts any valid token and simply resolves
 * the token's `sub` against its own table — so driver 5's token authenticated
 * as user 5 (a different person) and read their profile. The guard alone is not
 * an identity boundary here; this middleware is.
 *
 * Tokens issued before audience scopes existed carry NO scope and are rejected,
 * which signs those sessions out. That is deliberate: accepting an unscoped
 * token is exactly the hole being closed.
 */
class EnsureTokenAudience
{
    public function handle(Request $request, Closure $next, string $audience): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->deny('unauthenticated', 'Unauthenticated.', 401);
        }

        // Only a BEARER credential can carry (or lack) an audience. When the
        // caller was authenticated some other way there is no token to inspect
        // and nothing to confuse: `actingAs()` in tests injects the model
        // directly. This is not a bypass — the guards on these routes are
        // passport-only, so a real HTTP client must present a bearer token to
        // get this far, and that path is always checked below.
        if ($request->bearerToken() === null) {
            return $next($request);
        }

        $token = method_exists($user, 'token') ? $user->token() : null;

        if ($token === null || ! $token->can($audience)) {
            return $this->deny(
                'wrong_token_audience',
                'This access token was not issued for this application. Please sign in again.',
                403
            );
        }

        return $next($request);
    }

    private function deny(string $code, string $message, int $status): Response
    {
        return response()->json([
            'status' => false,
            'statusCode' => $status,
            'message' => $message,
            'data' => null,
            'error' => ['code' => $code, 'message' => $message],
            'meta' => null,
            'locale' => app()->getLocale(),
        ], $status);
    }
}
