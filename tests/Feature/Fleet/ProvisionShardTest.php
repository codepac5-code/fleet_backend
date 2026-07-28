<?php

namespace Tests\Feature\Fleet;

use Illuminate\Support\Facades\Artisan;

class ProvisionShardTest extends FleetTestCase
{
    public function test_command_is_registered(): void
    {
        $this->assertArrayHasKey('fleet:shard-provision', Artisan::all());
    }

    public function test_without_a_target_it_fails_cleanly(): void
    {
        $code = Artisan::call('fleet:shard-provision');

        $this->assertSame(1, $code);
    }
}
