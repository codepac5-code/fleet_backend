<?php

namespace App\Http\Services\Panel\Admin\Plans\Controller;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;

class TogglePlanController extends Controller
{
    public function __invoke(int $plan): RedirectResponse
    {
        $model = SubscriptionPlan::query()->find($plan);

        if ($model !== null) {
            $model->is_active = ! $model->is_active;
            $model->save();
        }

        return back()->with('status', textByLanguage('تم تحديث الخطة', 'Plan updated'));
    }
}
