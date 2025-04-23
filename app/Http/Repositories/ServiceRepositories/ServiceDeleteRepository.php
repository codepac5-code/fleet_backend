<?php
namespace App\Http\Repositories\ServiceRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Service;

class ServiceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Service();
    }
}
