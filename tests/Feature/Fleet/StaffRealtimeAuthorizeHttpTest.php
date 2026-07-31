<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\StaffRealtimeToken;
use Tests\TestCase;

/**
 * The panel authorizes its realtime rooms with a StaffRealtimeToken over the
 * SAME endpoint riders and drivers use with Passport tokens — and Passport was
 * eating it. On a bearer it cannot validate, `TokenGuard` does
 * `$request->headers->set('Authorization', '', true)`, erasing the header
 * before the staff branch ever looks at it. Every panel room answered DENIED,
 * so no live view in the dashboard ever received an event, and the only trace
 * was an "authorization server denied the request" line in the log.
 *
 * These go over HTTP on purpose: the bug lived in the middleware/guard chain,
 * not in the authorizer, so a unit test of the authorizer could never see it.
 */
class StaffRealtimeAuthorizeHttpTest extends TestCase
{
    private function authorize(string $token, string $channel)
    {
        return $this->postJson('realtime/authorize', ['channel' => $channel], [
            'Authorization' => 'Bearer ' . $token,
        ]);
    }

    public function test_an_admin_token_still_works_after_the_passport_guards_run(): void
    {
        $token = StaffRealtimeToken::mint('admin', 0, '');

        $this->authorize($token, 'admin')
            ->assertStatus(200)
            ->assertJsonPath('authorized', true)
            ->assertJsonPath('identity.type', 'admin');
    }

    public function test_an_office_token_authorizes_its_own_room_only(): void
    {
        $token = StaffRealtimeToken::mint('office', 1, '');

        $this->authorize($token, 'office.1')
            ->assertStatus(200)
            ->assertJsonPath('authorized', true);

        $this->authorize($token, 'office.2')
            ->assertStatus(403)
            ->assertJsonPath('authorized', false);
    }

    public function test_an_office_token_cannot_take_the_fleet_admin_room(): void
    {
        $token = StaffRealtimeToken::mint('office', 1, '');

        $this->authorize($token, 'admin')->assertStatus(403);
    }

    public function test_a_garbage_bearer_is_unauthenticated(): void
    {
        $this->authorize('not-a-token', 'admin')->assertStatus(401);
    }
}
