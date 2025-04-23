<?php
namespace App\Http\Repositories\RatingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Rating;

class RatingDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Rating();
    }
}