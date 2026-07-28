<?php

namespace Database\Seeders\Production;

use App\Http\Core\Const\Subscription\PlanKey;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PlanKey::CATALOG as $key => $plan) {
            SubscriptionPlan::query()->firstOrCreate(
                ['key' => $key],
                [
                    'name' => $plan['name'],
                    'price_minor' => $plan['price_minor'],
                    'currency_code' => 'USD',
                    'fleet_commission_rate' => $plan['fleet_rate'],
                    'driver_limit' => $plan['driver_limit'],
                    'trial_days' => $plan['price_minor'] ? 14 : null,
                    'is_active' => true,
                    'is_popular' => $key === PlanKey::BUSINESS,
                    'sort' => $plan['sort'],
                ]
            );
        }
    }
}
