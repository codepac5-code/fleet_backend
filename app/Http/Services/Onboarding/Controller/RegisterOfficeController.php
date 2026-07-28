<?php

namespace App\Http\Services\Onboarding\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Billing\RegionBilling;
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

        return redirect()->route('panel.office.subscription.show')
            ->with('status', textByLanguage('تم إنشاء حسابك! اختر خطة لبدء تجربتك المجانية.', 'Account created! Choose a plan to start your free trial.'));
    }
}
