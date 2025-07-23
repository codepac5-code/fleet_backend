<?php
namespace App\Http\Repositories\IssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Issue;

class IssueUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Issue();
    }

}