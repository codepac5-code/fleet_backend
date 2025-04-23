<?php
namespace App\Http\Repositories\UserReportRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\UserReport;

class UserReportDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new UserReport();
    }
}