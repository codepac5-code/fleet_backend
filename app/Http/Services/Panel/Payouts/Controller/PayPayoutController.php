<?php

namespace App\Http\Services\Panel\Payouts\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Payment\PayoutService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class PayPayoutController extends Controller
{
    public function __invoke(int $payout, PayoutService $payouts): RedirectResponse
    {
        try {
            $payouts->pay($payout);
        } catch (Throwable $e) {
            return back()->with('error', textByLanguage('تعذّر صرف الدفعة.', 'Could not pay out.'));
        }

        return back()->with('status', textByLanguage('تم صرف الدفعة.', 'Payout paid.'));
    }
}
