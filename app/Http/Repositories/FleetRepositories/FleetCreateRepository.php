<?php
namespace App\Http\Repositories\FleetRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Fleet;

class FleetCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Fleet();
    }
}