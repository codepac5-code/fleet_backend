<?php
namespace App\Http\Repositories\DriverAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\DriverAddress;

class DriverAddressCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new DriverAddress();
    }
}