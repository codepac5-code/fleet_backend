<?php

namespace App\Http\Services\Panel\Admin\Growth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\ReferralSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SaveReferralSettingsController extends Controller
{
    public function __invoke(Request $request, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'referrer_reward' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'invitee_reward' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'qualifying_rides' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $conn = TenantConnection::current();
        $settings = ReferralSetting::on($conn)->orderBy('id')->first() ?? (new ReferralSetting())->setConnection($conn);

        $settings->is_active = (bool) ($data['is_active'] ?? false);
        $settings->referrer_reward_minor = (int) round(((float) ($data['referrer_reward'] ?? 0)) * 100);
        $settings->invitee_reward_minor = (int) round(((float) ($data['invitee_reward'] ?? 0)) * 100);
        $settings->qualifying_rides = (int) $data['qualifying_rides'];
        $settings->save();

        $audit->record('referrals.settings_saved', 'admin', (int) (auth('admin')->id() ?? 0), null, null, [
            'active' => $settings->is_active,
            'referrer_minor' => $settings->referrer_reward_minor,
            'invitee_minor' => $settings->invitee_reward_minor,
        ], $request->ip());

        return back()->with('status', textByLanguage('تم حفظ برنامج الإحالة', 'Referral programme saved'));
    }
}
