<?php
namespace App\Http\Repositories\SliderRepositories;

use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Slider;

class SliderUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Slider();
    }

    public function change_status($id , $status) : bool{
        $model = $this->model->find($id);

        if( $model == null) return false;
        $model->isActive = $status;
        $model->save();

        return true;
    }

}
