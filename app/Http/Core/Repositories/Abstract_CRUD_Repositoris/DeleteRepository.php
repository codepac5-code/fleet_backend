<?php
namespace App\Http\Core\Repositories\Abstract_CRUD_Repositoris;

use Illuminate\Database\Eloquent\Model;
use App\Http\Core\Const\Messages\ErrorMessages;

abstract class DeleteRepository {


    protected Model $model;


    public function delete_multiple_records_by_Key( $key , $values){
       return $this->model->query()->whereIn($key , $values )->delete();
    }

    public function force_delete_multiple_records_by_Key( $key , $values){
        return $this->model->query()->whereIn($key , $values )->forceDelete();
     }

     public function restor_multiple_records_by_Key( $key , $values){
        return $this->model->query()->whereIn($key , $values )->forceDelete();
     }

     public function forceDelete( $conditions){
        return $this->model->where( $conditions )->forceDelete();
     }

     public function delete( $conditions){
      return $this->model->where( $conditions )->delete();
   }


}
