<?php
namespace App\Http\Repositories\FleetStatisticRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\FleetStatistic;

class FleetStatisticReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new FleetStatistic();
    }

}