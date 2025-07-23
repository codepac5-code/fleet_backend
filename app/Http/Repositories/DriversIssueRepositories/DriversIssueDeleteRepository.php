<?php
namespace App\Http\Repositories\DriversIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\DriversIssue;

class DriversIssueDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new DriversIssue();
    }
}