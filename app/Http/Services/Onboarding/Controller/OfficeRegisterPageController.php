<?php

namespace App\Http\Services\Onboarding\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Models\InfrastructureNode;
use Illuminate\Contracts\View\View;

class OfficeRegisterPageController extends Controller
{
    public function __invoke(): View
    {
        $countries = InfrastructureNode::query()
            ->where('type', 'country')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn ($node) => RegionBilling::isSubscription($node))
            ->map(fn ($node) => ['id' => $node->id, 'name' => $node->name, 'code' => $node->country_code])
            ->values()
            ->all();

        return view('web-site.office-register', [
            'countries' => $countries,
        ]);
    }
}
