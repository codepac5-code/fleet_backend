<?php
namespace App\Http\Repositories\UserReportRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\UserReport;

class UserReportUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new UserReport();
    }

}