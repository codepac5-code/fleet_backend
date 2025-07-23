<?php
namespace App\Http\Repositories\DepartmentRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Department;

class DepartmentUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Department();
    }

}