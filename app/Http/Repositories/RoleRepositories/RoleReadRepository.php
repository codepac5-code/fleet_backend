<?php
namespace App\Http\Repositories\RoleRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Role;

class RoleReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Role();
    }

}