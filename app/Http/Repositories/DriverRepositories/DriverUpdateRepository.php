<?php
namespace App\Http\Repositories\DriverRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;

use App\Models\Driver;

class DriverUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Driver();
    }

}
