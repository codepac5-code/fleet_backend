<?php

namespace App\Http\Services\Panel\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateServiceController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        return view('panel.services.form', [
            'entity'  => $scope->guard(),
            'user'    => $scope->user(),
            'service' => null,
        ]);
    }
}
