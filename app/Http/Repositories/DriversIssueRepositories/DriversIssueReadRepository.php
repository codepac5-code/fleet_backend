<?php
namespace App\Http\Repositories\DriversIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\DriversIssue;

class DriversIssueReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new DriversIssue();
    }

}