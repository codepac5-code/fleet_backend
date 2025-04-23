<?php
namespace App\Http\Repositories\VehicleRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Vehicle;

class VehicleCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Vehicle();
    }


    public function addVehicleSubServices( $vehicleId , $SubServiceIds){
        $vehicle = $this->model::findOrFail($vehicleId);

        $vehicle->subServices()->sync($SubServiceIds);
    }

}