<?php
namespace App\Http\Repositories\FleetStatisticRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\FleetStatistic;

class FleetStatisticUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new FleetStatistic();
    }

}