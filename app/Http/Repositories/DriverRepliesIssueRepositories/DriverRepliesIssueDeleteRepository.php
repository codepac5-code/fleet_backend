<?php
namespace App\Http\Repositories\DriverRepliesIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\DriverRepliesIssue;

class DriverRepliesIssueDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new DriverRepliesIssue();
    }
}