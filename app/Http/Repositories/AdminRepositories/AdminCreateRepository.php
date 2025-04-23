<?php
namespace App\Http\Repositories\AdminRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Admin;

class AdminCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Admin();
    }
}
