<?php
namespace App\Http\Repositories\ServiceRepositories;

use App\Models\Service;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;

class ServiceUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Service();
    }

    public function change_status($id , $status) : bool{
        $service = $this->model->find($id);

        if( $service == null) return false;
        $service->status = $status;
        $service->save();

        return true;
    }
   

}
