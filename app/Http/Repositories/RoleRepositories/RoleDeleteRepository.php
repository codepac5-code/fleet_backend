<?php
namespace App\Http\Repositories\RoleRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Role;

class RoleDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Role();
    }
}