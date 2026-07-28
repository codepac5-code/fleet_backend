<?php
namespace App\Http\Repositories\TravelRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\TravelRoutes;

class TravelRoutesDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new TravelRoutes();
    }
}