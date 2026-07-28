<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Auth\TokenAudience;
use App\Http\Middleware\EnsureTokenAudience;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Guards the fix for the audience hole.
 *
 * Passport enforces a token's provider only when the ISSUING CLIENT declares
 * one, and this install's personal-access client has a NULL provider. So every
 * passport guard accepts any valid token and just resolves `sub` against its own
 * table — driver 5's token authenticated as user 5 (a different person) and read
 * their profile. `EnsureTokenAudience` is what actually separates the two apps.
 */
class TokenAudienceTest extends TestCase
{
    /** A stand-in for the Passport token hanging off an authenticated model. */
    private function actor(?array $scopes): object
    {
        return new class($scopes) {
            public int $id = 5;

            public function __construct(private ?array $scopes)
            {
            }

            public function token(): ?object
            {
                if ($this->scopes === null) {
                    return null;
                }

                return new class($this->scopes) {
                    public function __construct(private array $scopes)
                    {
                    }

                    public function can(string $scope): bool
                    {
                        return in_array($scope, $this->scopes, true);
                    }
                };
            }
        };
    }

    private function statusFor(?array $scopes, string $required, bool $withBearer = true): int
    {
        $request = Request::create('/driver/home', 'GET');

        if ($withBearer) {
            $request->headers->set('Authorization', 'Bearer some-token');
        }

        $request->setUserResolver(fn () => $this->actor($scopes));

        $response = (new EnsureTokenAudience())->handle(
            $request,
            fn () => response()->json(['ok' => true]),
            $required
        );

        return $response->getStatusCode();
    }

    public function test_token_for_this_app_passes(): void
    {
        $this->assertSame(200, $this->statusFor([TokenAudience::DRIVER], TokenAudience::DRIVER));
    }

    /** The actual vulnerability: a driver token must not act on rider routes. */
    public function test_token_for_the_other_app_is_rejected(): void
    {
        $this->assertSame(403, $this->statusFor([TokenAudience::DRIVER], TokenAudience::USER));
    }

    /**
     * Tokens minted before audiences existed carry no scope. Rejecting them
     * signs those sessions out on purpose — honouring an unscoped token is
     * precisely the hole.
     */
    public function test_legacy_unscoped_token_is_rejected(): void
    {
        $this->assertSame(403, $this->statusFor([], TokenAudience::DRIVER));
    }

    public function test_missing_token_object_is_rejected(): void
    {
        $this->assertSame(403, $this->statusFor(null, TokenAudience::DRIVER));
    }

    /**
     * No bearer credential → nothing to scope (this is how `actingAs()` reaches
     * a route in tests). Not a bypass: these routes' guards are passport-only,
     * so a real client must present a bearer token, which is always checked.
     */
    public function test_non_bearer_request_is_left_to_the_guard(): void
    {
        $this->assertSame(200, $this->statusFor(null, TokenAudience::DRIVER, withBearer: false));
    }
}
