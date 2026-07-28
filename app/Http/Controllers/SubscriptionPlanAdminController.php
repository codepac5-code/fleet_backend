<?php

namespace App\Http\Controllers;

use App\Http\Core\Const\Subscription\PlanKey;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanAdminController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::query()->orderBy('sort')->orderBy('id')->get();

        return view('panel.admin.plans', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if (SubscriptionPlan::query()->where('key', $data['key'])->exists()) {
            return back()->withInput()->with('error', 'مفتاح الخطّة مستخدم مسبقاً / Plan key already exists.');
        }

        $this->clearPopularIfNeeded($data);
        SubscriptionPlan::query()->create($data);

        return back()->with('status', 'created');
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::query()->findOrFail($id);
        $data = $this->validated($request);

        if (SubscriptionPlan::query()->where('key', $data['key'])->where('id', '!=', $plan->id)->exists()) {
            return back()->withInput()->with('error', 'مفتاح الخطّة مستخدم مسبقاً / Plan key already exists.');
        }

        $this->clearPopularIfNeeded($data, $plan->id);
        $plan->update($data);

        return back()->with('status', 'updated');
    }

    public function destroy($id)
    {
        SubscriptionPlan::query()->findOrFail($id)->delete();

        return back()->with('status', 'deleted');
    }

    public function toggle($id)
    {
        $plan = SubscriptionPlan::query()->findOrFail($id);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return back()->with('status', 'toggled');
    }

    public function seed()
    {
        foreach (PlanKey::CATALOG as $key => $p) {
            SubscriptionPlan::query()->firstOrCreate(['key' => $key], [
                'name' => $p['name'],
                'price_minor' => $p['price_minor'],
                'currency_code' => 'USD',
                'fleet_commission_rate' => $p['fleet_rate'],
                'driver_limit' => $p['driver_limit'],
                'sort' => $p['sort'],
                'is_active' => true,
                'is_popular' => $key === PlanKey::BUSINESS,
            ]);
        }

        return back()->with('status', 'seeded');
    }

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'key' => 'required|string|max:32',
            'name' => 'required|string|max:64',
            'price' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|max:10',
            'fleet_commission_rate' => 'nullable|numeric|min:0|max:100',
            'driver_limit' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'sort' => 'nullable|integer|min:0',
        ]);

        return [
            'key' => $v['key'],
            'name' => $v['name'],
            'price_minor' => ($v['price'] ?? null) === null || $v['price'] === '' ? null : (int) round(((float) $v['price']) * 100),
            'currency_code' => $v['currency_code'] ?? 'USD',
            'fleet_commission_rate' => ($v['fleet_commission_rate'] ?? null) === null || $v['fleet_commission_rate'] === '' ? null : (float) $v['fleet_commission_rate'],
            'driver_limit' => ($v['driver_limit'] ?? null) === null || $v['driver_limit'] === '' ? null : (int) $v['driver_limit'],
            'trial_days' => ($v['trial_days'] ?? null) === null || $v['trial_days'] === '' ? null : (int) $v['trial_days'],
            'sort' => (int) ($v['sort'] ?? 0),
            'is_active' => $request->boolean('is_active'),
            'is_popular' => $request->boolean('is_popular'),
        ];
    }

    private function clearPopularIfNeeded(array $data, $exceptId = null): void
    {
        if (!empty($data['is_popular'])) {
            SubscriptionPlan::query()
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_popular' => false]);
        }
    }
}
