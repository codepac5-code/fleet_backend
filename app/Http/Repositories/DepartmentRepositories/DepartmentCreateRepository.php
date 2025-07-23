<?php
namespace App\Http\Repositories\DepartmentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Department;

class DepartmentCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Department();
    }
}