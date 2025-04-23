<?php
namespace App\Http\Repositories\FleetStatisticRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\FleetStatistic;

class FleetStatisticDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new FleetStatistic();
    }
}