<?php
namespace App\Http\Repositories\EmployeeRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Employee;

class EmployeeDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Employee();
    }
}