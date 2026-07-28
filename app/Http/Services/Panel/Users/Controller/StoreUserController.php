<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Users\Logic\UserRepository;
use App\Http\Services\Panel\Users\Request\StoreUserRequest;
use Illuminate\Http\RedirectResponse;

class StoreUserController extends Controller
{
    public function __invoke(StoreUserRequest $request, EntityScope $scope, UserRepository $users): RedirectResponse
    {
        $users->create($request->validated());

        return redirect()
            ->route("panel.{$scope->guard()}.user.index")
            ->with('status', textByLanguage('تمت إضافة المستخدم بنجاح', 'User created successfully'));
    }
}
