<?php
namespace App\Http\Repositories\ServiceRepositories;

use App\Http\Core\Const\Options\Roles;
use App\Models\Service;
use App\Models\SubService;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class ServiceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Service();
    }



    

    public function getDatatableServices(  $filter)
    {
        $query = $this->model->query();
        

        if ($filter != null) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

       return $query->orderBy('created_at','desc');

    }


    public function change_status($id , $status) : bool{
        $service = $this->find($id);

        if( $service == null) return false;
        $service->status = $status;
        $service->save();

        return true;
    }

}
