<?php
namespace App\Http\Repositories\AdminRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Admin;

class AdminDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Admin();
    }
}
