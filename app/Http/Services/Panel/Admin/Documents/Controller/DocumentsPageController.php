<?php

namespace App\Http\Services\Panel\Admin\Documents\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Documents\Logic\DocumentRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DocumentsPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, DocumentRepository $documents): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('panel.documents.index', [
            'entity'    => $scope->guard(),
            'user'      => $scope->user(),
            'search'    => $search,
            'documents' => $documents->paginate($search ?: null),
        ]);
    }
}
