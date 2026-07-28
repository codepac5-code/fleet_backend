<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\OfficeKycDocument;
use Illuminate\Http\RedirectResponse;

class DeleteOfficeDocumentController extends Controller
{
    public function __invoke(int $office, int $document): RedirectResponse
    {
        $doc = OfficeKycDocument::on(TenantConnection::current())
            ->where('officeId', $office)
            ->find($document);

        if ($doc !== null) {
            $doc->delete();
        }

        return back()->with('status', textByLanguage('تم حذف المستند', 'Document deleted'));
    }
}
