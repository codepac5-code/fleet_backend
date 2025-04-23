<?php
namespace App\Http\Repositories\regionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\region;

class regionUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new region();
    }

}