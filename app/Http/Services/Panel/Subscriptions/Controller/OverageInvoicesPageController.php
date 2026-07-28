<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\Office;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OverageInvoicesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, PlanOverageService $overage): View
    {
        $status = $request->query('status');
        $status = $status === 'collected' || $status === 'invoiced' ? (string) $status : null;

        $invoices = $overage->invoices($status);

        $officeNames = Office::query()
            ->whereIn('id', array_values(array_unique(array_column($invoices, 'office_id'))))
            ->pluck('officeName', 'id');

        $pendingMinor = 0;
        $collectedMinor = 0;
        foreach ($invoices as $invoice) {
            if ($invoice['status'] === 'collected') {
                $collectedMinor += $invoice['total_minor'];
            } else {
                $pendingMinor += $invoice['total_minor'];
            }
        }

        return view('panel.admin.overage-invoices.index', [
            'entity' => $scope->guard(),
            'invoices' => $invoices,
            'officeNames' => $officeNames,
            'statusFilter' => $status,
            'pendingMinor' => $pendingMinor,
            'collectedMinor' => $collectedMinor,
        ]);
    }
}
