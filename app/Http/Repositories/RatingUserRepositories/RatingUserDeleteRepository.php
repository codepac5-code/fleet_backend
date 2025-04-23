<?php
namespace App\Http\Repositories\RatingUserRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\RatingUser;

class RatingUserDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new RatingUser();
    }
}