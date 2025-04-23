<?php
namespace App\Http\Repositories\StateRepositories;
use App\Models\State;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class StateReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new State();
    }


    public function get_states( $countryId , $value ){
    
            $items =State::select('id', 'name as text');
            if ($countryId == null ) {
                $items->where('countryId',$countryId);
            }
            if ($value != '') {
                $items->where('name', 'LIKE', $value . '%');
            }
           return  $items->get();

    }
      
      

}