<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Drivers\Request\UpdateDriverRequest;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class UpdateDriverController extends Controller
{
    public function __invoke(UpdateDriverRequest $request, int $driver, EntityScope $scope, DriverRepository $drivers): RedirectResponse
    {
        $model = $drivers->findOrFail($driver);

        $data = $request->validated();

        if (! $scope->isAdmin()) {
            unset($data['officeId']);
        }

        $drivers->update($model, $data);

        return redirect()
            ->route("panel.{$scope->guard()}.driver.index")
            ->with('status', textByLanguage('تم تحديث السائق بنجاح', 'Driver updated successfully'));
    }
}
