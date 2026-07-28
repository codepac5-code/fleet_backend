<?php

namespace App\Http\Services\Panel\Payouts\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Payment\PayoutService;
use App\Http\Core\Const\Ledger\AccountType;
use App\Http\Core\Const\Options\Guard;
use App\Http\Core\GeoServices\ShardManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RequestOfficePayoutController extends Controller
{
    public function __invoke(Request $request, PayoutService $payouts): RedirectResponse
    {
        $officeId = (int) Auth::guard(Guard::$Office)->id();
        $amount = (int) $request->input('amount_minor');
        $currency = strtoupper((string) ($request->input('currency_code') ?: ShardManager::currency()));

        if ($amount <= 0) {
            return back()->with('error', textByLanguage('المبلغ يجب أن يكون موجباً.', 'Amount must be positive.'));
        }

        try {
            $payouts->request('office', $officeId, AccountType::REVENUE, $amount, $currency);
        } catch (Throwable $e) {
            $message = $e->getMessage() === 'insufficient_balance'
                ? textByLanguage('الرصيد غير كافٍ.', 'Insufficient balance.')
                : textByLanguage('تعذّر إنشاء طلب الصرف.', 'Could not request payout.');

            return back()->with('error', $message);
        }

        return back()->with('status', textByLanguage('تم إرسال طلب الصرف.', 'Payout requested.'));
    }
}
