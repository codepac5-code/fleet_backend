<?php
namespace App\Http\Repositories\DriverJobApplicationRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\DriverJobApplication;

class DriverJobApplicationCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new DriverJobApplication();
    }
}