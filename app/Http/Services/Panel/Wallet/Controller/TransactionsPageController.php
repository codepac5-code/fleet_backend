<?php

namespace App\Http\Services\Panel\Wallet\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Wallet\Logic\TransactionRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransactionsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, TransactionRepository $transactions): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '') ?: null;

        return view('panel.wallet.index', [
            'entity'       => $scope->guard(),
            'user'         => $scope->user(),
            'isAdmin'      => $scope->isAdmin(),
            'search'       => $search,
            'statusFilter' => $status,
            'summary'      => $transactions->summary(),
            'transactions' => $transactions->paginate($search ?: null, $status),
            // Subscription money is a ledger posting, not a legacy wallet row —
            // it belongs on this screen even though it cannot page with them.
            'subscriptionPayments' => $transactions->subscriptionPayments(),
        ]);
    }
}
