<?php
namespace App\Http\Repositories\UserReportRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\UserReport;

class UserReportCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new UserReport();
    }
}