<?php

namespace App\Http\Services\Panel\Admin\Offices\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateOfficeController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        return view('panel.offices.form', [
            'entity' => $scope->guard(),
            'user'   => $scope->user(),
            'office' => null,
        ]);
    }
}
