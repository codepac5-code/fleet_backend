<?php
namespace App\Http\Repositories\EmployeeRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Employee;

class EmployeeCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Employee();
    }
}