<?php

namespace App\Http\Middleware\Panel;

use App\Http\Core\Classes\Security\TwoFactorService;
use App\Http\Services\Panel\Security\Logic\StaffIdentity;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Closure;
use Illuminate\Http\Request;

/**
 * Holds a signed-in staff member on their security page until they enrol, when
 * the platform policy requires it. Off by default, and the security + logout
 * routes always pass, so the policy can never strand anyone mid-panel.
 */
class RequireTwoFactorEnrollment
{
    public function __construct(
        private TwoFactorService $twoFactor,
        private StaffIdentity $identity,
        private EntityScope $scope
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $guard = $this->scope->guard();

        if ($guard === null || ! $this->identity->isRequired($guard)) {
            return $next($request);
        }

        if ($request->routeIs('*.security.*', 'panel.logout', 'panel.locale')) {
            return $next($request);
        }

        if ($this->twoFactor->isEnabled($guard, $this->identity->id($this->scope))) {
            return $next($request);
        }

        return redirect()->route("panel.{$guard}.security.index")
            ->with('status', textByLanguage(
                'تفعيل التحقق بخطوتين مطلوب قبل متابعة استخدام اللوحة.',
                'Two-factor authentication must be set up before you can continue.'
            ));
    }
}
