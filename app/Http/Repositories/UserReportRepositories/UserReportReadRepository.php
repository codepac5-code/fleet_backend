<?php
namespace App\Http\Repositories\UserReportRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\UserReport;

class UserReportReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new UserReport();
    }

}