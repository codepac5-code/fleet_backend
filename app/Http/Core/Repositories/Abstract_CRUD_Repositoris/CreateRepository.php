<?php
namespace App\Http\Core\Repositories\Abstract_CRUD_Repositoris;

use Illuminate\Database\Eloquent\Model;
use App\Http\Core\Const\Messages\ErrorMessages;


abstract class CreateRepository {

    protected Model $model;


    public function create (array $data){
        $model = $this->model->query()->create(
            $data
        );
        return ($model)?$model:make_exception(ErrorMessages::getKey(ErrorMessages::$SomeThingWentWrong));
    }


    public function updateOrCreate($uniqe_data  , $data ){
        return $this->model->query()->updateOrCreate($uniqe_data ,$data);
    }

    public function insertOrIgnore($data = []){
        return $this->model->insertOrIgnore($data);
    }
    



}

