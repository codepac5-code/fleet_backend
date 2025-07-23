<?php
namespace App\Http\Repositories\IssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Issue;

class IssueDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Issue();
    }
}