<?php

namespace Tests\Feature\Fleet;

use App\Http\Services\Panel\Users\Logic\UserRepository;
use App\Models\User;

class UserBlockTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_23_085910_create_users_table.php',
        '2026_07_25_000004_add_block_reason_to_users_table.php',
    ];

    private function makeUser(): User
    {
        return User::query()->create([
            'firstName' => 'Test', 'lastName' => 'Rider', 'phoneNumber' => '+97455123456',
            'dialCode' => '+974', 'password' => 'x', 'isActive' => 1,
        ]);
    }

    public function test_blocking_records_reason_and_timestamp(): void
    {
        $repo = new UserRepository();
        $user = $this->makeUser();

        $repo->toggleStatus($user, 'chargeback fraud');

        $this->assertSame(0, (int) $user->isActive);
        $this->assertSame('chargeback fraud', $user->block_reason);
        $this->assertNotNull($user->blocked_at);
    }

    public function test_reinstating_clears_reason_and_timestamp(): void
    {
        $repo = new UserRepository();
        $user = $this->makeUser();

        $repo->toggleStatus($user, 'spam');
        $repo->toggleStatus($user);

        $this->assertSame(1, (int) $user->isActive);
        $this->assertNull($user->block_reason);
        $this->assertNull($user->blocked_at);
    }

    public function test_blocking_without_a_reason_leaves_it_null(): void
    {
        $repo = new UserRepository();
        $user = $this->makeUser();

        $repo->toggleStatus($user, '');

        $this->assertSame(0, (int) $user->isActive);
        $this->assertNull($user->block_reason);
        $this->assertNotNull($user->blocked_at);
    }
}
