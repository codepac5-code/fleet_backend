<?php
namespace App\Http\Repositories\FleetOfficeRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\FleetOffice;

class FleetOfficeReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new FleetOffice();
    }

}