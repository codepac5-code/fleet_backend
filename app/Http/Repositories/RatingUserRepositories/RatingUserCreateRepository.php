<?php
namespace App\Http\Repositories\RatingUserRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\RatingUser;

class RatingUserCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new RatingUser();
    }
}