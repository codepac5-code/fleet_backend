<?php

namespace App\Http\Services\Panel\Regions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\InfrastructureNode;
use Illuminate\Contracts\View\View;

class RegionsBillingPageController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        $countries = InfrastructureNode::query()
            ->where('type', 'country')
            ->orderBy('name')
            ->get()
            ->map(fn ($node) => [
                'id' => $node->id,
                'name' => $node->name,
                'country_code' => $node->country_code,
                'is_active' => (bool) $node->is_active,
                'mode' => RegionBilling::mode($node),
            ])
            ->all();

        return view('panel.regions.index', [
            'entity' => $scope->guard(),
            'countries' => $countries,
        ]);
    }
}
