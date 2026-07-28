<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\GeoServices\ShardManager;
use App\Models\InfrastructureNode;
use Illuminate\Http\Request;

class ShardResolutionTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
        '2026_06_25_000011_add_currency_to_infrastructure_nodes.php',
    ];

    private function seedCountries(): void
    {
        InfrastructureNode::query()->create(['type' => 'country', 'name' => 'Qatar', 'country_code' => 'qa', 'currency_code' => 'QAR', 'is_active' => true]);
        InfrastructureNode::query()->create(['type' => 'country', 'name' => 'UAE', 'country_code' => 'ae', 'currency_code' => 'AED', 'is_active' => true]);
    }

    private function request(?string $country = null): Request
    {
        $request = Request::create('/x', 'GET');

        if ($country !== null) {
            $request->headers->set('X-Country', $country);
        }

        return $request;
    }

    public function test_x_country_header_resolves_the_correct_shard(): void
    {
        $this->seedCountries();

        $node = ShardManager::resolveFromRequest($this->request('ae'));

        $this->assertNotNull($node);
        $this->assertSame('ae', strtolower((string) $node->country_code));
        $this->assertSame('AED', strtoupper((string) $node->currency_code));
    }

    public function test_missing_header_does_not_bind_to_any_shard(): void
    {
        $this->seedCountries();

        $this->assertNull(ShardManager::resolveFromRequest($this->request()));
    }

    public function test_unknown_country_returns_null_not_a_wrong_shard(): void
    {
        $this->seedCountries();

        $this->assertNull(ShardManager::resolveFromRequest($this->request('zz')));
    }
}
