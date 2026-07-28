<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DocumentStatus;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use App\Models\OfficeKycDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreOfficeDocumentController extends Controller
{
    public function __invoke(int $office, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'file' => ['required', 'file', 'max:8192'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $conn = TenantConnection::current();
        Office::on($conn)->findOrFail($office);

        $doc = new OfficeKycDocument([
            'officeId' => $office,
            'name' => $data['name'],
            'file' => $request->file('file')->store('office-documents', 'public'),
            'status' => DocumentStatus::PENDING,
            'expires_at' => $data['expires_at'] ?? null,
        ]);
        $doc->setConnection($conn);
        $doc->save();

        return back()->with('status', textByLanguage('تم رفع المستند', 'Document uploaded'));
    }
}
