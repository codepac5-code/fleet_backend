<?php
namespace App\Http\Repositories\TravelRoutesRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\TravelRoutes;

class TravelRoutesUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new TravelRoutes();
    }

}