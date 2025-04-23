<?php
namespace App\Http\Repositories\DriverAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\DriverAddress;

class DriverAddressReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new DriverAddress();
    }

}