<?php
namespace App\Http\Repositories\VehicleRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Vehicle;

class VehicleUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Vehicle();
    }

}