<?php
namespace App\Http\Repositories\regionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\region;

class regionReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new region();
    }

}