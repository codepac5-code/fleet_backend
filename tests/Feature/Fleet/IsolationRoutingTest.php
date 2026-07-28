<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\GeoServices\ShardContext;
use App\Http\Core\GeoServices\ShardManager;
use App\Models\EventOutbox;
use App\Models\InfrastructureNode;
use App\Models\SubscriptionPlan;

class IsolationRoutingTest extends FleetTestCase
{
    protected function tearDown(): void
    {
        ShardContext::clear();
        parent::tearDown();
    }

    public function test_global_model_always_uses_global_connection(): void
    {
        $this->assertSame('global', (new SubscriptionPlan())->getConnectionName());

        ShardContext::set($this->node());
        $this->assertSame('global', (new SubscriptionPlan())->getConnectionName());
    }

    public function test_shard_model_routes_to_dynamic_only_when_shard_active(): void
    {
        ShardContext::clear();
        $this->assertNotSame('dynamic', (new EventOutbox())->getConnectionName());

        ShardContext::set($this->node());
        $this->assertSame('dynamic', (new EventOutbox())->getConnectionName());

        ShardContext::clear();
        $this->assertNotSame('dynamic', (new EventOutbox())->getConnectionName());
    }

    public function test_currency_resolves_from_active_shard_with_default_fallback(): void
    {
        ShardContext::clear();
        $this->assertSame('USD', ShardManager::currency());

        ShardContext::set($this->node('aed'));
        $this->assertSame('AED', ShardManager::currency());
    }

    private function node(?string $currency = null): InfrastructureNode
    {
        return new InfrastructureNode([
            'type' => 'country',
            'country_code' => 'qa',
            'currency_code' => $currency,
            'is_active' => true,
        ]);
    }
}
