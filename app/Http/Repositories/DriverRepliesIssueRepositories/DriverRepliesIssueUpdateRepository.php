<?php
namespace App\Http\Repositories\DriverRepliesIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\DriverRepliesIssue;

class DriverRepliesIssueUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new DriverRepliesIssue();
    }

}