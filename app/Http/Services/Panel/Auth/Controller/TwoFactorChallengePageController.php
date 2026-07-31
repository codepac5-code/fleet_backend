<?php

namespace App\Http\Services\Panel\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Auth\Logic\TwoFactorChallenge;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TwoFactorChallengePageController extends Controller
{
    public function __invoke(TwoFactorChallenge $challenge): View|RedirectResponse
    {
        if ($challenge->pending() === null) {
            return redirect()->route('panel.login');
        }

        return view('panel.auth.two-factor');
    }
}
