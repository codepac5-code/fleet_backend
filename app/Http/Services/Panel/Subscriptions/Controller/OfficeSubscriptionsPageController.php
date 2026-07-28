<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Core\Const\Subscription\SubscriptionStatus;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\Office;
use App\Models\OfficeSubscription;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OfficeSubscriptionsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, PlanOverageService $overage): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;

        $query = OfficeSubscription::query()->orderByDesc('id');

        if ($status !== null) {
            $query->where('status', $status);
        }

        $subscriptions = $query->limit(300)->get();

        $officeNames = Office::query()
            ->whereIn('id', $subscriptions->pluck('office_id')->unique()->all())
            ->pluck('officeName', 'id');

        $countBy = fn (string $s) => OfficeSubscription::query()->where('status', $s)->count();

        return view('panel.admin.subscriptions.index', [
            'entity' => $scope->guard(),
            'subscriptions' => $subscriptions,
            'officeNames' => $officeNames,
            'overageByOffice' => $overage->pendingByOffice(),
            'statusFilter' => $status,
            'counts' => [
                'trialing' => $countBy(SubscriptionStatus::TRIALING),
                'active' => $countBy(SubscriptionStatus::ACTIVE),
                'past_due' => $countBy(SubscriptionStatus::PAST_DUE),
                'total' => OfficeSubscription::query()->count(),
            ],
        ]);
    }
}
