<?php

namespace App\Http\Services\Panel\Admin\Documents\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Documents\Logic\DocumentRepository;
use Illuminate\Http\RedirectResponse;

class ToggleDocumentStatusController extends Controller
{
    public function __invoke(int $document, DocumentRepository $documents): RedirectResponse
    {
        $documents->toggleStatus($documents->findOrFail($document));

        return back();
    }
}
