<?php

namespace App\Http\Services\Onboarding\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
use App\Http\Core\Const\Subscription\PlanKey;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\InfrastructureNode;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterOfficeController extends Controller
{
    public function __invoke(Request $request, OfficeRepository $offices): RedirectResponse
    {
        $data = $request->validate([
            'office_name' => ['required', 'string', 'max:120'],
            'contact_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'city' => ['nullable', 'string', 'max:120'],
            'country_id' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan' => ['nullable', 'string', 'max:40'],
        ]);

        $node = InfrastructureNode::query()
            ->where('id', $data['country_id'])
            ->where('type', 'country')
            ->where('is_active', true)
            ->first();

        if ($node === null || ! RegionBilling::isSubscription($node)) {
            return back()->withErrors(['country_id' => textByLanguage('المنطقة غير متاحة للتسجيل الذاتي.', 'This region is not open for self-registration.')])->withInput();
        }

        ShardManager::activate($node);

        $exists = Office::on(TenantConnection::current())
            ->where('email', $data['email'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['email' => textByLanguage('هذا البريد مسجّل بالفعل.', 'This email is already registered.')])->withInput();
        }

        $office = $offices->create([
            'officeName' => $data['office_name'],
            'displayName' => $data['contact_name'],
            'email' => $data['email'],
            'contactNumber' => $data['phone'],
            'city' => $data['city'] ?? null,
            'country' => $node->country_code,
            'region' => $node->name,
            'status' => 1,
            'password' => $data['password'],
        ]);

        session(['active_shard_id' => $node->id]);

        Auth::guard('office')->login($office);

        $request->session()->regenerate();

        // Carry the plan chosen on the website's pricing grid into the
        // subscription page so it lands pre-selected on the exact plan the office
        // signed up for — the free trial / checkout still runs there, but the
        // office doesn't have to re-pick.
        $plan = $data['plan'] ?? null;
        $planParam = ($plan !== null && PlanKey::exists($plan)) ? ['plan' => $plan] : [];

        return redirect()->route('panel.office.subscription.show', $planParam)
            ->with('status', textByLanguage('تم إنشاء حسابك! أكمل تفعيل خطتك لبدء تجربتك المجانية.', 'Account created! Confirm your plan to start your free trial.'));
    }
}
