<?php

namespace App\Http\Services\Panel\Payouts\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Payment\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RejectPayoutController extends Controller
{
    public function __invoke(Request $request, int $payout, PayoutService $payouts): RedirectResponse
    {
        $payouts->reject($payout, $request->input('note'));

        return back()->with('status', textByLanguage('تم رفض الدفعة.', 'Payout rejected.'));
    }
}
