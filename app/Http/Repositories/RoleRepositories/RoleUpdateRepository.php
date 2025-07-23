<?php
namespace App\Http\Repositories\RoleRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Role;

class RoleUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Role();
    }

}