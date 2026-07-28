<?php
namespace App\Http\Repositories\TravelRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\TravelRoutes;

class TravelRoutesCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new TravelRoutes();
    }
}