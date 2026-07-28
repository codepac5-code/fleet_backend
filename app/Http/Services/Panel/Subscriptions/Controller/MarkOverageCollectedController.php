<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkOverageCollectedController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, PlanOverageService $overage, AuditLogService $audit, string $ref): RedirectResponse
    {
        $count = $overage->markCollected($ref);

        if ($count > 0) {
            $audit->record(
                'overage.collected',
                'admin',
                null,
                'overage_invoice',
                null,
                ['invoice_ref' => $ref, 'items' => $count],
                $request->ip()
            );
        }

        return redirect()
            ->route("panel.{$scope->guard()}.overage-invoices.index")
            ->with('status', $count > 0
                ? textByLanguage('تم تعليم الفاتورة كمُحصّلة.', 'Invoice marked collected.')
                : textByLanguage('لا توجد بنود بانتظار التحصيل لهذه الفاتورة.', 'No items awaiting collection for this invoice.'));
    }
}
