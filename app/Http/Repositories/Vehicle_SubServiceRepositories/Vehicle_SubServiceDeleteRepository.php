<?php
namespace App\Http\Repositories\Vehicle_SubServiceRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Vehicle_SubService;

class Vehicle_SubServiceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Vehicle_SubService();
    }
}