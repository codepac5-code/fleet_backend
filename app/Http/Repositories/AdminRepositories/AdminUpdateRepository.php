<?php
namespace App\Http\Repositories\AdminRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Admin;

class AdminUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Admin();
    }

}
