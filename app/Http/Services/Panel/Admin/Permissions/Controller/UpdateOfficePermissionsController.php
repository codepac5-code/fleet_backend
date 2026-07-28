<?php

namespace App\Http\Services\Panel\Admin\Permissions\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Const\Options\Guard;
use App\Http\Core\Const\Options\Roles;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Permissions\Logic\PermissionMatrix;
use App\Http\Services\Panel\Admin\Permissions\Request\UpdateOfficePermissionsRequest;
use Illuminate\Http\RedirectResponse;

class UpdateOfficePermissionsController extends Controller
{
    public function __invoke(UpdateOfficePermissionsRequest $request, int $office, OfficeRepository $offices, PermissionMatrix $matrix): RedirectResponse
    {
        $model = $offices->findOrFail($office);

        $matrix->sync($model, $request->selected(), Guard::$Office, Roles::Office->value);

        return redirect()
            ->route('panel.admin.office.index')
            ->with('status', textByLanguage('تم تحديث صلاحيات المكتب', 'Office permissions updated'));
    }
}
