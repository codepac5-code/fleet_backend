<?php

namespace App\Http\Services\Panel\Security\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Security\TwoFactorService;
use App\Http\Services\Panel\Security\Logic\StaffIdentity;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DisableTwoFactorController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, TwoFactorService $twoFactor, StaffIdentity $identity, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $guard = (string) $scope->guard();
        $staffId = $identity->id($scope);

        // Turning the second factor OFF is itself a sensitive action, so it costs
        // a live code — a hijacked session cannot quietly remove it.
        if (! $twoFactor->verify($guard, $staffId, $data['code'])) {
            return back()->withErrors(['code' => textByLanguage('الرمز غير صحيح.', 'That code is not valid.')]);
        }

        if ($identity->isRequired($guard)) {
            return back()->withErrors(['code' => textByLanguage('التحقق بخطوتين إلزامي لحسابك ولا يمكن إيقافه.', 'Two-factor authentication is mandatory for your account and cannot be turned off.')]);
        }

        $twoFactor->disable($guard, $staffId);
        $audit->record('staff.2fa_disabled', $guard, $staffId, null, null, [], $request->ip());

        return back()->with('status', textByLanguage('تم إيقاف التحقق بخطوتين', 'Two-factor authentication is off'));
    }
}
