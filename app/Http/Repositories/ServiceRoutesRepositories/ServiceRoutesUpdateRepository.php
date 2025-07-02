<?php
namespace App\Http\Repositories\ServiceRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\ServiceRoutes;

class ServiceRoutesUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new ServiceRoutes();
    }

}