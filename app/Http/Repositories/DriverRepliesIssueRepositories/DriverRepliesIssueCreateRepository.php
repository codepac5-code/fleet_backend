<?php
namespace App\Http\Repositories\DriverRepliesIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\DriverRepliesIssue;

class DriverRepliesIssueCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new DriverRepliesIssue();
    }
}