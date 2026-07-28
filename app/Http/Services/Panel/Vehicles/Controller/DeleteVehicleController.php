<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Vehicles\Logic\VehicleRepository;
use Illuminate\Http\RedirectResponse;

class DeleteVehicleController extends Controller
{
    public function __invoke(int $vehicle, EntityScope $scope, VehicleRepository $vehicles): RedirectResponse
    {
        $vehicles->delete($vehicles->findOrFail($vehicle));

        return redirect()
            ->route("panel.{$scope->guard()}.vehicle.index")
            ->with('status', textByLanguage('تم حذف المركبة', 'Vehicle deleted'));
    }
}
