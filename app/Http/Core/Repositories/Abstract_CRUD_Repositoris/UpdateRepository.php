<?php
namespace App\Http\Core\Repositories\Abstract_CRUD_Repositoris;

use Illuminate\Database\Eloquent\Model;
use App\Http\Core\Const\Messages\ErrorMessages;

abstract class UpdateRepository {

    protected Model $model;


    public function updateFirst($conditions , array $data){
        
        return $this->model::where($conditions)->first()->update($data);
     }

    public function update($conditions , array $data){
        
       return $this->model::where($conditions)->update($data);
    }


    public function update_by_Id($id , array $data){
        
        return $this->model::find($id)->update($data);
     }
 
     public function updateOrCreate( $data ){
        $result = $this->model->query()->updateOrCreate($data);
        return $result;
    }

     
    public function update_multiple_records_by_Key( $key , $values , $new_data){
        
        $model = $this->model->query()->whereIn($key , $values )->update( $new_data);
        return $model!=0 ? $model : make_exception(ErrorMessages::getKey(ErrorMessages::$SomeThingWentWrong));
    }
}

