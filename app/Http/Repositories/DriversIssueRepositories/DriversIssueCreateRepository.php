<?php
namespace App\Http\Repositories\DriversIssueRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\DriversIssue;

class DriversIssueCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new DriversIssue();
    }
}