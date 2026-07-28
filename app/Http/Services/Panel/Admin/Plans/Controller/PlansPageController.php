<?php

namespace App\Http\Services\Panel\Admin\Plans\Controller;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\View\View;

class PlansPageController extends Controller
{
    public function __invoke(): View
    {
        // Plans are global (platform-wide, SubscriptionPlan is on `global`).
        return view('panel.plans.index', [
            'plans' => SubscriptionPlan::query()->orderBy('sort')->orderBy('id')->get(),
        ]);
    }
}
