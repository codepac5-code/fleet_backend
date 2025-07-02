<?php
namespace App\Http\Repositories\ServiceRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\ServiceRoutes;

class ServiceRoutesCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new ServiceRoutes();
    }
}