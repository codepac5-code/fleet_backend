<?php

namespace App\Http\Services\Panel\Admin\Plans\Controller;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SavePlanController extends Controller
{
    public function __invoke(Request $request, ?int $plan = null): RedirectResponse
    {
        $model = $plan !== null ? SubscriptionPlan::query()->findOrFail($plan) : new SubscriptionPlan();

        $data = $request->validate([
            'key' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/', Rule::unique('subscription_plans', 'key')->ignore($model->id)],
            'name' => ['required', 'string', 'max:80'],
            'price_minor' => ['nullable', 'integer', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'fleet_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'driver_limit' => ['nullable', 'integer', 'min:0'],
            'ride_limit' => ['nullable', 'integer', 'min:0'],
            'extra_ride_minor' => ['nullable', 'integer', 'min:0'],
            'extra_driver_minor' => ['nullable', 'integer', 'min:0'],
            'trial_days' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
        ]);

        // Blank numeric limits/prices mean "unlimited / not set" — keep them null
        // rather than 0 so an Enterprise plan can be truly unlimited.
        $nullable = ['price_minor', 'fleet_commission_rate', 'driver_limit', 'ride_limit', 'extra_ride_minor', 'extra_driver_minor'];

        $model->fill([
            'key' => $data['key'],
            'name' => $data['name'],
            'currency_code' => $data['currency_code'] ?? null,
            'trial_days' => (int) ($data['trial_days'] ?? 0),
            'sort' => (int) ($data['sort'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'is_popular' => $request->boolean('is_popular'),
        ]);

        foreach ($nullable as $field) {
            $model->{$field} = ($data[$field] ?? null) === null || $data[$field] === '' ? null : $data[$field];
        }

        $model->save();

        return redirect()
            ->route('panel.admin.plans.index')
            ->with('status', textByLanguage('تم حفظ الخطة', 'Plan saved'));
    }
}
