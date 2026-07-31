<?php

namespace App\Http\Services\Panel\Admin\Growth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Referral;
use App\Models\ReferralSetting;
use App\Models\User;
use Illuminate\View\View;

/**
 * The country's referral programme: its rules (per shard, in the local currency)
 * and what it has actually paid out. Referral rows are global, so the list is
 * filtered to the active country the same way complaints are.
 */
class ReferralsPageController extends Controller
{
    public function __invoke(): View
    {
        $settings = ReferralSetting::on(TenantConnection::current())->orderBy('id')->first() ?? new ReferralSetting();
        $country = Referral::activeCountryCode();

        $referrals = Referral::query()
            ->when($country !== null, fn ($q) => $q->where(fn ($w) => $w->where('country_code', $country)->orWhereNull('country_code')))
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $names = User::query()
            ->whereIn('id', $referrals->pluck('referrer_user_id')->merge($referrals->pluck('invitee_user_id'))->unique()->all())
            ->get(['id', 'firstName', 'lastName', 'phoneNumber'])
            ->keyBy('id');

        return view('panel.growth.referrals', [
            'settings' => $settings,
            'currency' => ShardManager::currency(),
            'referrals' => $referrals,
            'names' => $names,
            'counts' => [
                'pending' => $referrals->where('status', Referral::PENDING)->count(),
                'rewarded' => $referrals->where('status', Referral::REWARDED)->count(),
                'paidMinor' => (int) $referrals->sum(fn ($r) => (int) $r->referrer_reward_minor + (int) $r->invitee_reward_minor),
            ],
        ]);
    }
}
