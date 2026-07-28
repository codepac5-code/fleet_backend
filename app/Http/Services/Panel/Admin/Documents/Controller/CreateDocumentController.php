<?php

namespace App\Http\Services\Panel\Admin\Documents\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateDocumentController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        return view('panel.documents.form', [
            'entity'   => $scope->guard(),
            'user'     => $scope->user(),
            'document' => null,
        ]);
    }
}
