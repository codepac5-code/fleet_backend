<?php
namespace App\Http\Repositories\ServiceRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\ServiceRoutes;

class ServiceRoutesDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new ServiceRoutes();
    }
}