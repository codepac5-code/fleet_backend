<?php
namespace App\Http\Repositories\VehicleBrandRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\VehicleBrand;

class VehicleBrandDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new VehicleBrand();
    }
}