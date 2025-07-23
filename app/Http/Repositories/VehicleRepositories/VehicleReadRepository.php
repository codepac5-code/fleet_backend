<?php
namespace App\Http\Repositories\VehicleRepositories;
use App\Models\Vehicle;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class VehicleReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Vehicle();
    }


    public function getOfficeBookings($id){
       return $this->model->query()->where('id', $id)->with(['officeBooking' => function($query) {
            $query->orderBy('updated_at', 'desc');
        }])->first(); 
    }


    public function dataTableVehicle (){
        // $auth = auth()->user();

        $query = $this->model->scopeForCurrentUser();

       return $query->orderBy('created_at','desc');
    }

}