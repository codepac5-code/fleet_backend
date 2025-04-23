<?php
namespace App\Http\Repositories\DriverPayoutRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\DriverPayout;

class DriverPayoutDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new DriverPayout();
    }
}