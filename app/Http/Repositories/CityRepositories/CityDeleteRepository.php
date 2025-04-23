<?php
namespace App\Http\Repositories\CityRepositories;
use App\Repositories\basic\DeleteRepository;
use App\Models\City;

class CityDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new City();
    }
}