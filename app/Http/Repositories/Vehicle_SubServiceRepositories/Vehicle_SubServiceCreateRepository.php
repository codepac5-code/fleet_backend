<?php
namespace App\Http\Repositories\Vehicle_SubServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Vehicle_SubService;

class Vehicle_SubServiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Vehicle_SubService();
    }
}