<?php
namespace App\Http\Repositories\IssueLogRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\IssueLog;

class IssueLogCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new IssueLog();
    }
}