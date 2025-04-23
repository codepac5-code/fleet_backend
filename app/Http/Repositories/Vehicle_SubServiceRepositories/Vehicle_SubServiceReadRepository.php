<?php
namespace App\Http\Repositories\Vehicle_SubServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Vehicle_SubService;

class Vehicle_SubServiceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Vehicle_SubService();
    }

}