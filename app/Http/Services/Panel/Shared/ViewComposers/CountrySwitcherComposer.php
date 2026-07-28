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
            $countries = InfrastructureNode::query()
                ->where('type', 'country')
                ->where('is_active', true)
                ->get(['id', 'name', 'country_code']);
        } catch (\Throwable $e) {
            $countries = collect();
        }

        $activeCountryId = session('active_shard_id') ?? optional($countries->first())->id;

        $view->with('panelCountries', $countries)
            ->with('activeCountryId', $activeCountryId)
            ->with('allCountriesScope', \App\Http\Core\GeoServices\ShardAggregator::SCOPE);
    }
}
