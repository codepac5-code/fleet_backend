<?php
namespace App\Http\Repositories\DriverPayoutRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\DriverPayout;

class DriverPayoutCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new DriverPayout();
    }
}