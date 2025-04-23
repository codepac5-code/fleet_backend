<?php
namespace App\Http\Repositories\CityRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\City;

class CityCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new City();
    }
}