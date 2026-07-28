<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class CreateUserController extends Controller
{
    public function __invoke(EntityScope $scope): View
    {
        return view('panel.users.form', [
            'entity' => $scope->guard(),
            'user'   => $scope->user(),
            'record' => null,
        ]);
    }
}
