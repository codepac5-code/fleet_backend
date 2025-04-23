<?php
namespace App\Http\Repositories\FleetOfficeRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\FleetOffice;

class FleetOfficeDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new FleetOffice();
    }
}