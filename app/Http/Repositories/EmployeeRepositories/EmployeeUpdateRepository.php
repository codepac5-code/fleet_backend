<?php
namespace App\Http\Repositories\EmployeeRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Employee;

class EmployeeUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Employee();
    }

}