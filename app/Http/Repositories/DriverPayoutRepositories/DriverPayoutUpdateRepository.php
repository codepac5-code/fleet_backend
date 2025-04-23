<?php
namespace App\Http\Repositories\DriverPayoutRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\DriverPayout;

class DriverPayoutUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new DriverPayout();
    }

}