<?php
namespace App\Http\Repositories\VehicleRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Vehicle;

class VehicleDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Vehicle();
    }
}