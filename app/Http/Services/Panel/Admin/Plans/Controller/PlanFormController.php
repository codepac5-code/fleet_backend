<?php

namespace App\Http\Services\Panel\Admin\Plans\Controller;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\View\View;

class PlanFormController extends Controller
{
    public function __invoke(?int $plan = null): View
    {
        $model = $plan !== null ? SubscriptionPlan::query()->findOrFail($plan) : null;

        return view('panel.plans.form', [
            'plan' => $model,
        ]);
    }
}
