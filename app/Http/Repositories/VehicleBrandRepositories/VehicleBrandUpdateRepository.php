<?php
namespace App\Http\Repositories\VehicleBrandRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\VehicleBrand;

class VehicleBrandUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new VehicleBrand();
    }

}