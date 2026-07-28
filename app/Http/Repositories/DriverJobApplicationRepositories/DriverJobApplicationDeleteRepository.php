<?php
namespace App\Http\Repositories\DriverJobApplicationRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\DriverJobApplication;

class DriverJobApplicationDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new DriverJobApplication();
    }
}