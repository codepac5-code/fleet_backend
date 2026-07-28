<?php

namespace App\Http\Services\Panel\Subscriptions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Subscription\PlanOverageService;
use App\Http\Services\Panel\Shared\Export\CsvExport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportOverageInvoicesController extends Controller
{
    public function __invoke(Request $request, PlanOverageService $overage): StreamedResponse
    {
        $status = $request->query('status');
        $status = $status === 'collected' || $status === 'invoiced' ? (string) $status : null;

        return CsvExport::stream(
            'overage-invoices.csv',
            ['Invoice', 'Office', 'Period', 'Amount', 'Currency', 'Items', 'Collection', 'External ref', 'Status', 'Invoiced', 'Collected'],
            $overage->exportRows($status)
        );
    }
}
