<?php
namespace App\Http\Repositories\DriverAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\DriverAddress;

class DriverAddressUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new DriverAddress();
    }

}