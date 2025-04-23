<?php
namespace App\Http\Repositories\UserRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\User;

class UserUpdateRepository extends UpdateRepository
{

    public function __construct()
    {
        $this->model = new User();
    }




}
