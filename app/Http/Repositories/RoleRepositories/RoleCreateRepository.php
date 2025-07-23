<?php
namespace App\Http\Repositories\RoleRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Role;

class RoleCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Role();
    }
}