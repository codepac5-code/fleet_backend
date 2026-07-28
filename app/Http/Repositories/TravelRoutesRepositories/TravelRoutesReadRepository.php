<?php
namespace App\Http\Repositories\TravelRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\TravelRoutes;

class TravelRoutesReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new TravelRoutes();
    }

}