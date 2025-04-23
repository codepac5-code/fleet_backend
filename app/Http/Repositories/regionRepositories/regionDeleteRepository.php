<?php
namespace App\Http\Repositories\regionRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\region;

class regionDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new region();
    }
}