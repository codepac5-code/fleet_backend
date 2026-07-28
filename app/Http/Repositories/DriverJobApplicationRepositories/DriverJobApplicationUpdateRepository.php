<?php
namespace App\Http\Repositories\DriverJobApplicationRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\DriverJobApplication;

class DriverJobApplicationUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new DriverJobApplication();
    }

}