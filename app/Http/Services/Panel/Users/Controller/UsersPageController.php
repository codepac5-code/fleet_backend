<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Users\Logic\UserRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UsersPageController extends Controller
{
    public function __invoke(Request $request, EntityScope $scope, UserRepository $users): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('panel.users.index', [
            'entity' => $scope->guard(),
            'user'   => $scope->user(),
            'search' => $search,
            'users'  => $users->paginate($search ?: null),
        ]);
    }
}
