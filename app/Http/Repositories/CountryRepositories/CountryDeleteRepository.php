<?php
namespace App\Http\Repositories\CountryRepositories;
use App\Repositories\basic\DeleteRepository;
use App\Models\Country;

class CountryDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Country();
    }
}