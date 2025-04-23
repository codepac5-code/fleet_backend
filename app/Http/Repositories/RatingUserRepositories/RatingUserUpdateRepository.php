<?php
namespace App\Http\Repositories\RatingUserRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\RatingUser;

class RatingUserUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new RatingUser();
    }

}