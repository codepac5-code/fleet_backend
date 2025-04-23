<?php
namespace App\Http\Repositories\regionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\region;

class regionCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new region();
    }
}