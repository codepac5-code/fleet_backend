<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Classes\Event\ChannelAuthorizer;
use PHPUnit\Framework\TestCase;

class ChannelAuthorizerTest extends TestCase
{
    private ChannelAuthorizer $authorizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizer = new ChannelAuthorizer();
    }

    public function test_user_may_join_only_its_own_channel(): void
    {
        $this->assertTrue($this->authorizer->authorize('user', 7, 'user.7'));
        $this->assertFalse($this->authorizer->authorize('user', 7, 'user.8'));
    }

    public function test_driver_may_join_only_its_own_channel(): void
    {
        $this->assertTrue($this->authorizer->authorize('driver', 9, 'driver.9'));
        $this->assertFalse($this->authorizer->authorize('driver', 9, 'driver.10'));
    }

    public function test_user_cannot_join_driver_channel_and_vice_versa(): void
    {
        $this->assertFalse($this->authorizer->authorize('user', 9, 'driver.9'));
        $this->assertFalse($this->authorizer->authorize('driver', 7, 'user.7'));
    }

    public function test_app_identities_cannot_join_office_or_admin_rooms(): void
    {
        $this->assertFalse($this->authorizer->authorize('user', 7, 'office.3'));
        $this->assertFalse($this->authorizer->authorize('driver', 9, 'office.3'));
        $this->assertFalse($this->authorizer->authorize('user', 7, 'admin'));
        $this->assertFalse($this->authorizer->authorize('driver', 9, 'admin'));
        // `admin.1` is malformed (the admin room carries no id) → denied.
        $this->assertFalse($this->authorizer->authorize('user', 7, 'admin.1'));
    }

    public function test_office_may_join_only_its_own_channel(): void
    {
        $this->assertTrue($this->authorizer->authorize('office', 3, 'office.3'));
        $this->assertFalse($this->authorizer->authorize('office', 3, 'office.4'));
        $this->assertFalse($this->authorizer->authorize('office', 3, 'user.3'));
        $this->assertFalse($this->authorizer->authorize('office', 3, 'driver.3'));
    }

    public function test_admin_may_join_the_admin_room_only(): void
    {
        $this->assertTrue($this->authorizer->authorize('admin', 0, 'admin'));
        $this->assertFalse($this->authorizer->authorize('admin', 0, 'office.3'));
        $this->assertFalse($this->authorizer->authorize('admin', 0, 'user.3'));
    }

    public function test_malformed_channels_are_denied(): void
    {
        $this->assertFalse($this->authorizer->authorize('user', 7, 'user'));
        $this->assertFalse($this->authorizer->authorize('user', 7, 'user.'));
        $this->assertFalse($this->authorizer->authorize('user', 7, 'user.abc'));
        $this->assertFalse($this->authorizer->authorize('user', 7, ''));
    }
}
