<?php
namespace App\Http\Repositories\SubServiceRepositories;

use App\Models\SubService;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;

class SubServiceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new SubService();
    }

    public function updateWithRoutes(){
        
    }


    public function change_status($id , $status) : bool{
        $service = $this->model->find($id);

        if( $service == null) return false;
        $service->status = $status;
        $service->save();

        return true;
    }

    public function saveImgePath($path , $model){
        $model->image = $path;
        $this->model->save();

    }


}
