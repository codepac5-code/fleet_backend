<?php
namespace App\Http\Repositories\DriverPayoutRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\DriverPayout;

class DriverPayoutReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new DriverPayout();
    }

}