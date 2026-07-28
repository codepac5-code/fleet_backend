<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Users\Logic\UserRepository;
use App\Http\Services\Panel\Users\Request\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;

class UpdateUserController extends Controller
{
    public function __invoke(UpdateUserRequest $request, int $user, EntityScope $scope, UserRepository $users): RedirectResponse
    {
        $model = $users->findOrFail($user);

        $users->update($model, $request->validated());

        return redirect()
            ->route("panel.{$scope->guard()}.user.index")
            ->with('status', textByLanguage('تم تحديث المستخدم بنجاح', 'User updated successfully'));
    }
}
