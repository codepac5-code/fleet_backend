<?php

namespace App\Http\Services\Panel\Auth\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Auth\Logic\TwoFactorChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class VerifyTwoFactorController extends Controller
{
    public function __invoke(Request $request, TwoFactorChallenge $challenge, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $pending = $challenge->pending();

        if ($pending === null) {
            return redirect()->route('panel.login');
        }

        // Six digits are guessable at speed; throttle per pending identity + IP.
        $key = 'panel-2fa:' . $pending['guard'] . ':' . $pending['id'] . ':' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $challenge->forget();

            return redirect()->route('panel.login')
                ->withErrors(['password' => textByLanguage('محاولات كثيرة — سجّل الدخول من جديد.', 'Too many attempts — please sign in again.')]);
        }

        $guard = $challenge->complete($data['code']);

        if ($guard === null) {
            RateLimiter::hit($key, 300);

            return back()->withErrors(['code' => textByLanguage('الرمز غير صحيح.', 'That code is not valid.')]);
        }

        RateLimiter::clear($key);
        $audit->record('staff.login', $guard, (int) $pending['id'], null, null, ['two_factor' => true], $request->ip());

        return redirect()->route("panel.{$guard}.home");
    }
}
