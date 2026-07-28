<?php

namespace App\Http\Services\Panel\Admin\Documents\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Documents\Logic\DocumentRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditDocumentController extends Controller
{
    public function __invoke(int $document, EntityScope $scope, DocumentRepository $documents): View
    {
        return view('panel.documents.form', [
            'entity'   => $scope->guard(),
            'user'     => $scope->user(),
            'document' => $documents->findOrFail($document),
        ]);
    }
}
