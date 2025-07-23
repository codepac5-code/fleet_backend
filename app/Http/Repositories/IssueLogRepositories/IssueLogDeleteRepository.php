<?php
namespace App\Http\Repositories\IssueLogRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\IssueLog;

class IssueLogDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new IssueLog();
    }
}