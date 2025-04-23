<?php
namespace App\Http\Repositories\RatingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Rating;

class RatingUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Rating();
    }

}