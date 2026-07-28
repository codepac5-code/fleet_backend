<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Vehicles\Logic\VehicleRepository;
use App\Http\Services\Panel\Vehicles\Request\StoreVehicleRequest;
use Illuminate\Http\RedirectResponse;

class StoreVehicleController extends Controller
{
    public function __invoke(StoreVehicleRequest $request, EntityScope $scope, VehicleRepository $vehicles): RedirectResponse
    {
        $data = $request->validated();

        if (! $scope->isAdmin()) {
            $data['officeId'] = $scope->officeId();
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('vehicles', 'public');
        }

        $vehicles->create($data);

        return redirect()
            ->route("panel.{$scope->guard()}.vehicle.index")
            ->with('status', textByLanguage('تمت إضافة المركبة بنجاح', 'Vehicle created successfully'));
    }
}
