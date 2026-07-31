<?php

namespace App\Http\Services\Onboarding\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Models\InfrastructureNode;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfficeRegisterPageController extends Controller
{
    public function __invoke(Request $request): View
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
            'selectedPlan' => $this->resolvePlan($request->query('plan')),
        ]);
    }

    /**
     * The plan the visitor picked on the pricing grid, carried through as `?plan=`.
     * Resolves to {key, name} from the DB catalog first, then the code catalog,
     * so the registration page can show and forward the chosen plan. Null when no
     * (or an unknown) plan was passed — registration still works, plan chosen after.
     */
    private function resolvePlan(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        $plan = SubscriptionPlan::query()->where('key', $key)->where('is_active', true)->first();

        if ($plan !== null) {
            return ['key' => $plan->key, 'name' => $plan->name];
        }

        if (PlanKey::exists($key)) {
            return ['key' => $key, 'name' => PlanKey::CATALOG[$key]['name']];
        }

        return null;
    }
}
