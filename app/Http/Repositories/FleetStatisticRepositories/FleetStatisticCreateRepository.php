<?php
namespace App\Http\Repositories\FleetStatisticRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\FleetStatistic;

class FleetStatisticCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new FleetStatistic();
    }
}