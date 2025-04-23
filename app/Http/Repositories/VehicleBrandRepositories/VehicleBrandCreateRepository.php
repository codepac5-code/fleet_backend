<?php
namespace App\Http\Repositories\VehicleBrandRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\VehicleBrand;

class VehicleBrandCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new VehicleBrand();
    }
}