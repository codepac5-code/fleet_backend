<?php
namespace App\Http\Repositories\CityRepositories;
use App\Models\City;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class CityReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new City();
    }

    public function get_cities( $stateId , $value ){

        $query = $this->model->select(['id' , 'name as text']);
        if ($stateId != null ) {
            $query = $query->where('stateId', $stateId);
        }

        if ($value != (null || '')) {
            $query = $query->where('name', 'LIKE', $value . '%');
        }
        return  $query->get();
    }
}