<?php

namespace App\Http\Services\Panel\Admin\Permissions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Guard;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Contracts\View\View;

class EditOfficePermissionsController extends Controller
{
    public function __invoke(int $office, EntityScope $scope, OfficeRepository $offices, PermissionMatrix $matrix): View
    {
        $model = $offices->findOrFail($office);

        return view('panel.offices.permissions', [
            'entity'  => $scope->guard(),
            'user'    => $scope->user(),
            'office'  => $model,
            'groups'  => $matrix->groups(Guard::$Office),
            'granted' => $matrix->granted($model, Guard::$Office),
        ]);
    }
}
