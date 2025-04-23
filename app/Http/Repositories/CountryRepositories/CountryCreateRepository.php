<?php
namespace App\Http\Repositories\CountryRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Country;

class CountryCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Country();
    }
}