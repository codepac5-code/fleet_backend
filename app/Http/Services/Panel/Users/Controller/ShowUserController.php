<?php

namespace App\Http\Services\Panel\Users\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\GeoServices\ShardManager;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Users\Logic\UserProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;

/**
 * Rider profile — the overview the panel had for drivers and offices but not for
 * riders. The user row is global; everything around it is read from the ACTIVE
 * country only.
 */
class ShowUserController extends Controller
{
    public function __invoke(int $user, EntityScope $scope, UserProfile $profile): View
    {
        $model = User::query()->findOrFail($user);
        $overview = $profile->overview($model);

        return view('panel.users.show', [
            'entity' => $scope->guard(),
            'isAdmin' => $scope->isAdmin(),
            'rider' => $model,
            'overview' => $overview,
            'rides' => $profile->recentRides($model),
            'ratings' => $profile->ratings($model),
            'walletMinor' => $profile->walletMinor($model, $overview['currency'] ?? ShardManager::currency()),
            'walletCurrency' => $overview['currency'] ?? ShardManager::currency(),
            'support' => $profile->support($model),
        ]);
    }
}
