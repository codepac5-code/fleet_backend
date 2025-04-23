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
        $auth = auth()->user();
        if($auth->hasAnyRole(['super-admin'])){
            $query = Vehicle::query();
        }
        elseif($auth->hasAnyRole(['office'])){
            $query = Vehicle::query()->where(['officeId'=>$auth->id]);
        }

        // if ($filter != null) {
        //     if (isset($filter['column_status'])) {
        //         $query->where('isConected', $filter['column_status']);
        //     }
        // }

       return $query->orderBy('created_at','desc');
    }

}