<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Users\Logic\UserRepository;
use Illuminate\Http\RedirectResponse;

class DeleteUserController extends Controller
{
    public function __invoke(int $user, EntityScope $scope, UserRepository $users): RedirectResponse
    {
        $users->delete($users->findOrFail($user));

        return redirect()
            ->route("panel.{$scope->guard()}.user.index")
            ->with('status', textByLanguage('تم حذف المستخدم', 'User deleted'));
    }
}
