<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\Office;
use App\Models\OfficeSubscription;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfficeSubscriptionsPageController extends Controller
{
    private const ENDING_SOON_DAYS = 7;

    public function __invoke(Request $request, EntityScope $scope, PlanOverageService $overage): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;

        $query = OfficeSubscription::query()->orderByDesc('id');

        if ($status !== null) {
            $query->where('status', $status);
        }

        $subscriptions = $query->limit(300)->get();
        $entitled = OfficeSubscription::query()->whereIn('status', SubscriptionStatus::ENTITLED)->get();

        $officeNames = Office::query()
            ->whereIn('id', $subscriptions->pluck('office_id')->merge($entitled->pluck('office_id'))->unique()->all())
            ->pluck('officeName', 'id');

        $countBy = fn (string $s) => OfficeSubscription::query()->where('status', $s)->count();
        $overagePending = $overage->pendingByOffice();

        return view('panel.admin.subscriptions.index', [
            'entity' => $scope->guard(),
            'mode' => RegionBilling::mode(),
            'currency' => ShardManager::currency(),
            'subscriptions' => $subscriptions,
            'officeNames' => $officeNames,
            'overageByOffice' => $overagePending,
            'statusFilter' => $status,
            'counts' => [
                'trialing' => $countBy(SubscriptionStatus::TRIALING),
                'active' => $countBy(SubscriptionStatus::ACTIVE),
                'past_due' => $countBy(SubscriptionStatus::PAST_DUE),
                'total' => OfficeSubscription::query()->count(),
            ],
            // What the country earns, what is about to be decided, what is at
            // risk. A list of rows answers none of those.
            'money' => [
                'mrrMinor' => (int) $entitled->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::PAST_DUE])->sum('price_minor'),
                'atRiskMinor' => (int) $entitled->where('status', SubscriptionStatus::PAST_DUE)->sum('price_minor'),
                'trialMinor' => (int) $entitled->where('status', SubscriptionStatus::TRIALING)->sum('price_minor'),
                'overageMinor' => (int) collect($overagePending)->sum(),
            ],
            'attention' => $this->attention($entitled, $officeNames),
        ]);
    }

    /**
     * The rows somebody has to act on: a trial about to end, a failed payment,
     * and — the one nothing surfaced before — an office trading in a
     * subscription country with no subscription at all.
     */
    private function attention($entitled, $officeNames): array
    {
        $endingSoon = [];
        $pastDue = [];

        foreach ($entitled as $subscription) {
            $name = $officeNames[$subscription->office_id] ?? ('#' . $subscription->office_id);

            if ($subscription->status === SubscriptionStatus::PAST_DUE) {
                $pastDue[] = ['office_id' => (int) $subscription->office_id, 'office' => $name, 'plan' => $subscription->plan_key];

                continue;
            }

            if ($subscription->status !== SubscriptionStatus::TRIALING || $subscription->trial_ends_at === null) {
                continue;
            }

            $days = (int) ceil(Carbon::now()->floatDiffInDays($subscription->trial_ends_at, false));

            if ($days <= self::ENDING_SOON_DAYS) {
                $endingSoon[] = [
                    'office_id' => (int) $subscription->office_id,
                    'office' => $name,
                    'plan' => $subscription->plan_key,
                    'days' => max(0, $days),
                ];
            }
        }

        usort($endingSoon, fn ($a, $b) => $a['days'] <=> $b['days']);

        if (! RegionBilling::isSubscription()) {
            return ['endingSoon' => $endingSoon, 'pastDue' => $pastDue, 'unsubscribed' => []];
        }

        $subscribed = $entitled->pluck('office_id')->map(fn ($id) => (int) $id)->unique()->all();

        $unsubscribed = Office::query()
            ->whereNotIn('id', $subscribed ?: [0])
            ->orderBy('id')
            ->limit(50)
            ->get(['id', 'officeName'])
            ->map(fn ($office) => ['office_id' => (int) $office->id, 'office' => $office->officeName])
            ->all();

        return ['endingSoon' => $endingSoon, 'pastDue' => $pastDue, 'unsubscribed' => $unsubscribed];
    }
}
