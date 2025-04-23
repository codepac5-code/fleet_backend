<?php
namespace App\Http\Repositories\DriverAddressRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\DriverAddress;

class DriverAddressDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new DriverAddress();
    }
}