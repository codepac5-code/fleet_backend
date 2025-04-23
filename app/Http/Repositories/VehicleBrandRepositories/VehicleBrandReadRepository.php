<?php
namespace App\Http\Repositories\VehicleBrandRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\VehicleBrand;

class VehicleBrandReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new VehicleBrand();
    }

}