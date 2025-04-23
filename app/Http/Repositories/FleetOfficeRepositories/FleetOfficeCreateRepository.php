<?php
namespace App\Http\Repositories\FleetOfficeRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\FleetOffice;

class FleetOfficeCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new FleetOffice();
    }
}