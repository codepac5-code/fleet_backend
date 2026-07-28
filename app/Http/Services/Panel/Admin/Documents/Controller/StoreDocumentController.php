<?php

namespace App\Http\Services\Panel\Admin\Documents\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Documents\Logic\DocumentRepository;
use App\Http\Services\Panel\Admin\Documents\Request\StoreDocumentRequest;
use Illuminate\Http\RedirectResponse;

class StoreDocumentController extends Controller
{
    public function __invoke(StoreDocumentRequest $request, DocumentRepository $documents): RedirectResponse
    {
        $documents->create($request->validated());

        return redirect()
            ->route('panel.admin.document.index')
            ->with('status', textByLanguage('تمت إضافة نوع المستند', 'Document type created'));
    }
}
