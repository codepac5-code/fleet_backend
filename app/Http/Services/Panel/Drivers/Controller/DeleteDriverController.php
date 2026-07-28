<?php

namespace App\Http\Services\Panel\Drivers\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Drivers\Logic\DriverRepository;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use Illuminate\Http\RedirectResponse;

class DeleteDriverController extends Controller
{
    public function __invoke(int $driver, EntityScope $scope, DriverRepository $drivers): RedirectResponse
    {
        $drivers->delete($drivers->findOrFail($driver));

        return redirect()
            ->route("panel.{$scope->guard()}.driver.index")
            ->with('status', textByLanguage('تم حذف السائق', 'Driver deleted'));
    }
}
