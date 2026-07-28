<?php

namespace App\Http\Services\Panel\Wallet\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Export\CsvExport;
use App\Http\Services\Panel\Wallet\Logic\TransactionRepository;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportTransactionsController extends Controller
{
    public function __invoke(TransactionRepository $transactions): StreamedResponse
    {
        return CsvExport::stream(
            'transactions.csv',
            ['ID', 'Reference', 'Amount', 'Status', 'From', 'To', 'Date'],
            $transactions->exportRows()
        );
    }
}
