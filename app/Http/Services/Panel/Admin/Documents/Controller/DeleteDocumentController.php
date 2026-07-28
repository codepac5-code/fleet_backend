<?php

namespace App\Http\Services\Panel\Admin\Documents\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Documents\Logic\DocumentRepository;
use Illuminate\Http\RedirectResponse;

class DeleteDocumentController extends Controller
{
    public function __invoke(int $document, DocumentRepository $documents): RedirectResponse
    {
        $documents->delete($documents->findOrFail($document));

        return redirect()
            ->route('panel.admin.document.index')
            ->with('status', textByLanguage('تم حذف نوع المستند', 'Document type deleted'));
    }
}
