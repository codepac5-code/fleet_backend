<?php

namespace App\Http\Services\Panel\Shared\ViewComposers;

use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\InfrastructureNode;
use Illuminate\View\View;

class CountrySwitcherComposer
{
    public function __construct(private EntityScope $scope) {}

    public function compose(View $view): void
    {
        if (! $this->scope->isAdmin()) {
            $view->with('panelCountries', collect())->with('activeCountryId', null);
            return;
        }

        try {
            // Only provisioned shards are switchable — an unprovisioned country
            // has no database, so offering it would let the admin switch into a
            // context that crashes every screen. It still appears on the Countries
            // screen (to be provisioned there).
            $countries = InfrastructureNode::query()
                ->where('type', 'country')
                ->where('is_active', true)
                ->whereNotNull('provisioned_at')
                ->get(['id', 'name', 'country_code', 'db_name']);
        } catch (\Throwable $e) {
            $countries = collect();
        }

        // Countries that were pointed at the SAME database see each other's
        // data, and "All countries" counts those rows ONCE — which reads as
        // missing records unless the panel says so out loud.
        $shared = $countries
            ->filter(fn ($node) => ! empty($node->db_name))
            ->groupBy(fn ($node) => strtolower((string) $node->db_name))
            ->filter(fn ($group) => $group->count() > 1)
            ->map(fn ($group) => $group->pluck('name')->all())
            ->values()
            ->all();

        $activeCountryId = session('active_shard_id') ?? optional($countries->first())->id;

        $view->with('panelCountries', $countries)
            ->with('sharedDatabaseGroups', $shared)
            ->with('activeCountryId', $activeCountryId)
            ->with('allCountriesScope', \App\Http\Core\GeoServices\ShardAggregator::SCOPE);
    }
}
