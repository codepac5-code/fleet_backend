<?php
namespace App\Http\Repositories\SubServiceRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\SubService;

class SubServiceDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new SubService();
    }
}
