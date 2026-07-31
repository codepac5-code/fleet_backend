<?php

namespace App\Http\Services\Panel\Security\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Security\TwoFactorService;
use App\Http\Services\Panel\Security\Logic\StaffIdentity;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

/**
 * A staff member's own security screen: enrol in 2FA, see recovery codes left,
 * or turn it off. Available to every panel guard.
 */
class SecurityPageController extends Controller
{
    public function __invoke(EntityScope $scope, TwoFactorService $twoFactor, StaffIdentity $identity): View
    {
        $guard = (string) $scope->guard();
        $staffId = (int) $identity->id($scope);
        $record = $twoFactor->record($guard, $staffId);

        return view('panel.security.index', [
            'entity' => $guard,
            'enabled' => $record?->isConfirmed() === true,
            'pending' => session('two_factor_setup'),
            'recoveryCodes' => session('two_factor_recovery'),
            'recoveryLeft' => $record !== null ? $twoFactor->remainingRecoveryCodes($record) : 0,
            'required' => $identity->isRequired($guard),
        ]);
    }
}
