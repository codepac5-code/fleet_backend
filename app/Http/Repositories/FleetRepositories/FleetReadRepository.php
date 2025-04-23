<?php
namespace App\Http\Repositories\FleetRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Fleet;

class FleetReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Fleet();
    }

}