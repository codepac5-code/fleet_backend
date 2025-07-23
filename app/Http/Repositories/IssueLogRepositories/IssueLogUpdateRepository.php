<?php
namespace App\Http\Repositories\IssueLogRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\IssueLog;

class IssueLogUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new IssueLog();
    }

}