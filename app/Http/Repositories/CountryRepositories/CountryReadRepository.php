<?php
namespace App\Http\Repositories\CountryRepositories;
use App\Models\Country;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class CountryReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Country();
    }


    public function get_countries( $value ){

        $items = Country::select('id', 'name as text');
        if ($value != '') {
            $items->where('name', 'LIKE', $value . '%');
        }
        return $items->get();

    }

}