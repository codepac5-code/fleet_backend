<?php

namespace App\Http\Services\Panel\Corporate\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Models\CorporateInvoice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CorporateInvoicesPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope): View
    {
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (string) $status : null;

        $invoices = CorporateInvoice::query()
            ->with('user:id,firstName,lastName,phoneNumber')
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->limit(300)
            ->get();

        $countBy = fn (string $s) => CorporateInvoice::query()->where('status', $s)->count();
        $dueSum = (int) CorporateInvoice::query()->where('status', 'due')->sum('amount_minor');

        return view('panel.corporate.invoices', [
            'entity' => $scope->guard(),
            'invoices' => $invoices,
            'statusFilter' => $status,
            'counts' => [
                'unbilled' => $countBy('unbilled'),
                'due' => $countBy('due'),
                'paid' => $countBy('paid'),
                'due_minor' => $dueSum,
            ],
        ]);
    }
}
