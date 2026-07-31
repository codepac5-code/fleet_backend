<?php

namespace App\Http\Services\Panel\Security\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Security\TwoFactorService;
use App\Http\Services\Panel\Security\Logic\StaffIdentity;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class StartTwoFactorController extends Controller
{
    public function __invoke(EntityScope $scope, TwoFactorService $twoFactor, StaffIdentity $identity): RedirectResponse
    {
        $setup = $twoFactor->beginEnrollment(
            (string) $scope->guard(),
            $identity->id($scope),
            $identity->label($scope)
        );

        if (isset($setup['already_enrolled'])) {
            return back()->with('status', textByLanguage('التحقق بخطوتين مفعّل بالفعل', 'Two-factor authentication is already on'));
        }

        // The secret only lives in the flash bag — a reload restarts enrolment
        // rather than leaving it sitting in a long-lived session.
        return back()->with('two_factor_setup', $setup);
    }
}
