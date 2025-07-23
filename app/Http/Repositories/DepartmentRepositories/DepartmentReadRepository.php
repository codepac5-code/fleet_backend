<?php
namespace App\Http\Repositories\DepartmentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Department;

class DepartmentReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Department();
    }

}