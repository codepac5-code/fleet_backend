<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DocumentStatus;
use App\Http\Services\Panel\Shared\Tenant\TenantConnection;
use App\Models\Office;
use App\Models\OfficeKycDocument;
use Illuminate\View\View;

class OfficeDocumentsPageController extends Controller
{
    public function __invoke(int $office): View
    {
        $conn = TenantConnection::current();

        $model = Office::on($conn)->findOrFail($office);

        $documents = OfficeKycDocument::on($conn)
            ->where('officeId', $office)
            ->latest('id')
            ->get();

        return view('panel.offices.documents', [
            'office' => $model,
            'documents' => $documents,
            'statusOptions' => DocumentStatus::all(),
        ]);
    }
}
