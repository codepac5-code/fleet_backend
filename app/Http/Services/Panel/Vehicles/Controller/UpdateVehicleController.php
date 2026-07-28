<?php

namespace App\Http\Services\Panel\Vehicles\Controller;

use App\Http\Controllers\Controller;
use App\Http\Services\Panel\Shared\Scoping\EntityScope;
use App\Http\Services\Panel\Vehicles\Logic\VehicleRepository;
use App\Http\Services\Panel\Vehicles\Request\UpdateVehicleRequest;
use Illuminate\Http\RedirectResponse;

class UpdateVehicleController extends Controller
{
    public function __invoke(UpdateVehicleRequest $request, int $vehicle, EntityScope $scope, VehicleRepository $vehicles): RedirectResponse
    {
        $model = $vehicles->findOrFail($vehicle);

        $data = $request->validated();

        if (! $scope->isAdmin()) {
            unset($data['officeId']);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('vehicles', 'public');
        }

        $vehicles->update($model, $data);

        return redirect()
            ->route("panel.{$scope->guard()}.vehicle.index")
            ->with('status', textByLanguage('تم تحديث المركبة بنجاح', 'Vehicle updated successfully'));
    }
}
