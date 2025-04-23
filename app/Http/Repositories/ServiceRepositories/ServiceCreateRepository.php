<?php
namespace App\Http\Repositories\ServiceRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Service;

class ServiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Service();
    }
}
