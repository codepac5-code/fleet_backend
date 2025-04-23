<?php
namespace App\Http\Repositories\RatingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Rating;

class RatingCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Rating();
    }
}