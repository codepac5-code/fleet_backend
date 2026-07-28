<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditOfficeController extends Controller
{
    public function __invoke(int $office, EntityScope $scope, OfficeRepository $offices): View
    {
        return view('panel.offices.form', [
            'entity' => $scope->guard(),
            'user'   => $scope->user(),
            'office' => $offices->findOrFail($office),
        ]);
    }
}
