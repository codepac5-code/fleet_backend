<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Audit\AuditLogService;
use App\Http\Services\Panel\Drivers\Logic\DocumentStatus;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\OfficeKycDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateOfficeDocumentStatusController extends Controller
{
    public function __invoke(int $office, int $document, Request $request, AuditLogService $audit): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', DocumentStatus::all())],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $conn = TenantConnection::current();

        $doc = OfficeKycDocument::on($conn)->where('officeId', $office)->findOrFail($document);
        $doc->status = $data['status'];
        $doc->note = $data['note'] ?? null;
        $doc->save();

        $audit->record('office.kyc_reviewed', 'admin', null, 'office', $office, [
            'document_id' => $document, 'status' => $data['status'],
        ], $request->ip());

        return back()->with('status', textByLanguage('تم تحديث حالة المستند', 'Document status updated'));
    }
}
