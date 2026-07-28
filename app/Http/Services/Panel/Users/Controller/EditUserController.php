<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Users\Logic\UserRepository;
use Illuminate\Contracts\View\View;

class EditUserController extends Controller
{
    public function __invoke(int $user, EntityScope $scope, UserRepository $users): View
    {
        return view('panel.users.form', [
            'entity' => $scope->guard(),
            'user'   => $scope->user(),
            'record' => $users->findOrFail($user),
        ]);
    }
}
