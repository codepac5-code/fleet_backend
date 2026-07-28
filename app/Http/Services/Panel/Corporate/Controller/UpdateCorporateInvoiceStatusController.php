<?php

namespace App\Http\Services\Panel\Corporate\Controller;

use App\Http\Controllers\Controller;
use App\Models\CorporateInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCorporateInvoiceStatusController extends Controller
{
    public function __invoke(Request $request, int $invoice): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:unbilled,due,paid'],
        ]);

        $record = CorporateInvoice::query()->findOrFail($invoice);
        $record->status = $data['status'];
        $record->save();

        return back()->with('status', textByLanguage('تم تحديث حالة الفاتورة.', 'Invoice status updated.'));
    }
}
