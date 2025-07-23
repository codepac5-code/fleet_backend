<?php
namespace App\Http\Repositories\DriversIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\DriversIssue;

class DriversIssueUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new DriversIssue();
    }

}