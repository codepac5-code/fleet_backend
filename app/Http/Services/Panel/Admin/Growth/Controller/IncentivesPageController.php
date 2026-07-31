<?php

namespace App\Http\Services\Panel\Admin\Growth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Incentive\IncentiveService;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\DriverIncentive;
use App\Models\DriverIncentiveProgress;
use Illuminate\View\View;

class IncentivesPageController extends Controller
{
    public function __invoke(IncentiveService $incentives): View
    {
        $conn = TenantConnection::current();
        $rules = DriverIncentive::on($conn)->orderByDesc('is_active')->orderBy('target_rides')->get();

        // Only the CURRENT window of each rule matters operationally — past
        // periods are history, and the payout total already covers them.
        $current = $rules->mapWithKeys(function (DriverIncentive $rule) use ($conn, $incentives) {
            $period = $incentives->period($rule->window);

            $rows = DriverIncentiveProgress::on($conn)
                ->where('incentive_id', $rule->id)
                ->where('period', $period)
                ->get(['rides', 'rewarded', 'reward_minor']);

            return [$rule->id => [
                'period' => $period,
                'drivers' => $rows->count(),
                'rewarded' => $rows->where('rewarded', true)->count(),
                'paidMinor' => (int) $rows->sum('reward_minor'),
            ]];
        });

        return view('panel.growth.incentives', [
            'rules' => $rules,
            'current' => $current,
            'currency' => ShardManager::currency(),
            'windows' => DriverIncentive::WINDOWS,
            'paidTotalMinor' => (int) DriverIncentiveProgress::on($conn)->where('rewarded', true)->sum('reward_minor'),
        ]);
    }
}
