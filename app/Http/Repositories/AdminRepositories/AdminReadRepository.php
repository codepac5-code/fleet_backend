<?php
namespace App\Http\Repositories\AdminRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Admin;

class AdminReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Admin();
    }

}
