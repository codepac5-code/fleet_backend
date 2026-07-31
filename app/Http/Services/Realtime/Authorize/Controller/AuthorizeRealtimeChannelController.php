<?php

namespace App\Http\Services\Realtime\Authorize\Controller;

use App\Http\Core\Classes\Event\ChannelAuthorizer;
use App\Http\Core\Classes\Event\StaffRealtimeToken;
use App\Http\Core\Const\Auth\TokenAudience;
use App\Http\Core\Const\Options\Guard;
use App\Http\Core\GeoServices\ShardManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthorizeRealtimeChannelController
{
    public function __construct(private ChannelAuthorizer $authorizer)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $identity = $this->resolveIdentity($request);

        if ($identity === null) {
            return response()->json(['authorized' => false], 401);
        }

        $channel = (string) $request->input('channel', '');
        $authorized = $channel !== '' && $this->authorizer->authorize($identity['type'], $identity['id'], $channel);

        return response()->json([
            'authorized' => $authorized,
            'identity' => $identity,
            // The namespace the client SHOULD be using. Apps used to derive it
            // themselves by lowercasing whatever country they sent, which only
            // matched the server by luck — a device sending `US` listened on
            // `us.*` while the server published `sa.*`. Handing it back lets a
            // client correct itself instead of going silently deaf.
            'shard' => ShardManager::shardKey(),
        ], $authorized ? 200 : 403);
    }

    /**
     * Who the bearer token belongs to — decided by the token's AUDIENCE, never
     * by guard order.
     *
     * This used to try the user guard first and return the first hit. Because
     * Passport's provider check is inert here (null-provider client), a driver's
     * token authenticates fine on the user guard whenever a rider shares the id
     * — so driver 5 was reported as `{type:user, id:5}` and denied its own
     * `{region}.driver.5` room, meaning it never received a ride offer. Trying
     * the driver guard first would only reverse who gets impersonated.
     */
    private function resolveIdentity(Request $request): ?array
    {
        // Read the bearer BEFORE the Passport guards run. On a token it cannot
        // validate, Passport does `$request->headers->set('Authorization', '')`
        // — it ERASES the header — so a panel's StaffRealtimeToken was already
        // gone by the time the staff path looked for it. Every panel room came
        // back DENIED and no live view in the dashboard received an event.
        $bearer = (string) $request->bearerToken();

        $candidates = [
            [Guard::$User, TokenAudience::USER, 'user'],
            [Guard::$Driver, TokenAudience::DRIVER, 'driver'],
        ];

        foreach ($candidates as [$guard, $audience, $type]) {
            $model = Auth::guard($guard)->user();

            if ($model === null) {
                continue;
            }

            $token = method_exists($model, 'token') ? $model->token() : null;

            if ($token !== null && $token->can($audience)) {
                return ['type' => $type, 'id' => (int) $model->id];
            }
        }

        return $this->resolveStaffIdentity($bearer);
    }

    /**
     * Panel staff authenticate with a short-lived StaffRealtimeToken instead of
     * a Passport token (the panel is web/session based). Runs only after the
     * app-token paths above miss, so it never affects rider/driver auth. The
     * token's baked-in shard must match the shard resolved for this request,
     * blocking cross-country replay.
     */
    private function resolveStaffIdentity(string $bearer): ?array
    {
        if ($bearer === '') {
            return null;
        }

        $staff = StaffRealtimeToken::verify($bearer);

        if ($staff === null) {
            return null;
        }

        $shard = ShardManager::shardKey();

        if ($shard !== '' && $staff['shard'] !== $shard) {
            return null;
        }

        return ['type' => $staff['type'], 'id' => $staff['id']];
    }
}
