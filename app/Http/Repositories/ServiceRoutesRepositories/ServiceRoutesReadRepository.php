<?php
namespace App\Http\Repositories\ServiceRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\ServiceRoutes;

class ServiceRoutesReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new ServiceRoutes();
    }

}