<?php
namespace App\Http\Repositories\UserRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\User;

class UserDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new User();
    }
}
