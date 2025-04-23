<?php
namespace app\Http\Repositories\SubServiceRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\SubService;

class SubServiceCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new SubService();
    }
}
