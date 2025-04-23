<?php
namespace App\Http\Repositories\UserRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\User;

class UserCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new User();
    }


    public function create ( array $data )
    {
        $user = $this->model->query()->create($data);
        return $user ;//?  make_exception($ErrorMessage::so):$user ;
    }
}
