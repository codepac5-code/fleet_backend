<?php

namespace App\Http\Services\Panel\Security\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Security\TwoFactorService;
use App\Http\Services\Panel\Security\Logic\StaffIdentity;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConfirmTwoFactorController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, TwoFactorService $twoFactor, StaffIdentity $identity, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        $guard = (string) $scope->guard();
        $staffId = $identity->id($scope);

        $codes = $twoFactor->confirm($guard, $staffId, $data['code']);

        if ($codes === null) {
            return back()->withErrors(['code' => textByLanguage('الرمز غير صحيح — جرّب رمزاً جديداً من التطبيق.', 'That code is not valid — try the next one from your app.')]);
        }

        $audit->record('staff.2fa_enabled', $guard, $staffId, null, null, [], $request->ip());

        return back()
            ->with('status', textByLanguage('تم تفعيل التحقق بخطوتين', 'Two-factor authentication is on'))
            ->with('two_factor_recovery', $codes);
    }
}
