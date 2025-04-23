<?php
namespace App\Http\Repositories\DriverRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;

use App\Models\Driver;

class DriverDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Driver();
    }
}
