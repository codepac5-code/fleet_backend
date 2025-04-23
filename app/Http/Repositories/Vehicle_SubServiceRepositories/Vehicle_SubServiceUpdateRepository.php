<?php
namespace App\Http\Repositories\Vehicle_SubServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Vehicle_SubService;

class Vehicle_SubServiceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Vehicle_SubService();
    }

}