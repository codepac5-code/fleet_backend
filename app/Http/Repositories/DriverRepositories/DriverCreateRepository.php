<?php
namespace App\Http\Repositories\DriverRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Driver;

class DriverCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Driver();
    }
}
