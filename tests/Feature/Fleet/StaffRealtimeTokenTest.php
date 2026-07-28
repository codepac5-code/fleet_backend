<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\StaffRealtimeToken;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class StaffRealtimeTokenTest extends TestCase
{
    public function test_mint_then_verify_roundtrips_the_identity(): void
    {
        $token = StaffRealtimeToken::mint('office', 5, 'sy');

        $this->assertSame(
            ['type' => 'office', 'id' => 5, 'shard' => 'sy'],
            StaffRealtimeToken::verify($token)
        );
    }

    public function test_admin_identity_roundtrips(): void
    {
        $token = StaffRealtimeToken::mint('admin', 0, 'qa');

        $this->assertSame('admin', StaffRealtimeToken::verify($token)['type']);
    }

    public function test_garbage_and_foreign_ciphertext_return_null(): void
    {
        $this->assertNull(StaffRealtimeToken::verify('not-a-token'));
        $this->assertNull(StaffRealtimeToken::verify(''));
    }

    public function test_expired_token_is_rejected(): void
    {
        $expired = Crypt::encryptString(json_encode([
            't' => 'office', 'i' => 5, 's' => 'sy', 'e' => time() - 10,
        ]));

        $this->assertNull(StaffRealtimeToken::verify($expired));
    }

    /**
     * The verify path is the only place a realtime identity type is minted for
     * staff — it must refuse to hand back an app-facing type even if one is
     * somehow encrypted with our key.
     */
    public function test_non_staff_type_is_refused(): void
    {
        $forged = Crypt::encryptString(json_encode([
            't' => 'driver', 'i' => 9, 's' => 'sy', 'e' => time() + 3600,
        ]));

        $this->assertNull(StaffRealtimeToken::verify($forged));
    }
}
