<?php
namespace App\Http\Repositories\DriverRepliesIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\DriverRepliesIssue;

class DriverRepliesIssueReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new DriverRepliesIssue();
    }

}