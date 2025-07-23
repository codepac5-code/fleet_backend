<?php
namespace App\Http\Repositories\DepartmentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Department;

class DepartmentDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Department();
    }
}