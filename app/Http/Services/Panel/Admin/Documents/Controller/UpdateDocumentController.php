<?php

namespace App\Http\Services\Panel\Admin\Documents\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Documents\Logic\DocumentRepository;
use App\Http\Services\Panel\Admin\Documents\Request\UpdateDocumentRequest;
use Illuminate\Http\RedirectResponse;

class UpdateDocumentController extends Controller
{
    public function __invoke(UpdateDocumentRequest $request, int $document, DocumentRepository $documents): RedirectResponse
    {
        $documents->update($documents->findOrFail($document), $request->validated());

        return redirect()
            ->route('panel.admin.document.index')
            ->with('status', textByLanguage('تم تحديث نوع المستند', 'Document type updated'));
    }
}
