<?php
namespace App\Http\Repositories\FleetRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Fleet;

class FleetUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Fleet();
    }

}