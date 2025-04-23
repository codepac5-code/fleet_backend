<?php
namespace app\Http\Repositories\SubServiceRepositories;

use App\Models\SubService;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class SubServiceReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new SubService();
    }


    public function get_sub_services_list(  $filter)
    {
        $auth = auth()->user();
        if($auth->hasAnyRole(['super-admin'])){
            $query = $this->model->query();
        }

        if ($filter != null) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }

       return $query->orderBy('created_at','desc');
    
    }

}
