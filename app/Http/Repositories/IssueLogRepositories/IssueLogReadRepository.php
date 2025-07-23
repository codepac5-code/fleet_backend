<?php
namespace App\Http\Repositories\IssueLogRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\IssueLog;

class IssueLogReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new IssueLog();
    }

}