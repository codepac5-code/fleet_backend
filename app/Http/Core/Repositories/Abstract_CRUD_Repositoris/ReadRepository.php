<?php
namespace App\Http\Core\Repositories\Abstract_CRUD_Repositoris;

use Illuminate\Database\Eloquent\Model;

abstract class ReadRepository
{
    protected Model $model;

    public function find(int $id , array $selected = ["*"]){
        return $this->model->select($selected)->find( $id );
    }

    public function findOr($id){
        return $this->model->findOrFail($id);
    }

    public function is_exists($conditions , array $selected = ["*"]){
       return $this->model::select($selected)
                ->where($conditions)
                ->exists();
    }

    public function getByConditions( $conditions =[] , array $selected = ["*"] ) {
        return $this->model->select($selected)
        ->where($conditions)->get();

    }

    public function getFirstByConditions( $conditions , array $selected = ["*"]  ) {
        return $this->model->select($selected)
        ->where($conditions)->first();
    }

    public function getWhereIn(string $column, array $values, array $selected = ["*"])
    {
        return $this->model->select($selected)
            ->whereIn($column, $values)
            ->get();
    }


    public function getByValue($column , $value): Model | null {
        $model = $this->model->where([$column => $value])->first();
        return $model;
    }

    public function getAllRecords(array $selected = ["*"]){
        return $model = $this->model->query()->select($selected)->get();
    }

    public function getAll(array $selected = ["*"] , array $with=[] , array $conditions=[] ){
        return $model = $this->model->query()->select($selected)->with($with)->where($conditions)->orderBy('updated_at','desc')->paginate(10);
    }



    public function getWithRelation(array $selected = ["*"] , array $with=[] , array $conditions=[] , $orderBy = []){
         $model = $this->model->query()->select($selected)->with($with)->where($conditions);
        if($orderBy != []){
            return $model->orderBy($orderBy,'desc')->paginate(10);
        }
        return $model->paginate(10);
    }

    public function getFirstWithRelation(array $selected = ["*"] , array $with=[] , array $conditions=[] , $orderBy = []){
        $model = $this->model->query()->select($selected)->with($with)->where($conditions);
       if($orderBy != []){
           return $model->orderBy($orderBy,'desc')->first();
       }
       return $model->first();
   }

    public function get_first_with_trashed($conditions , $action = 'Non'){
         $model = $this->model->query()->withTrashed()->where($conditions)->first();

        switch ($action) {

            case 'restore':
                $model->restore();
                break;

            case 'forcedelete':
                $model->forcedelete();
                break;

            default:
            return $model;
         }

    }


    public function countRecords($conditions , array $selected = ["*"] ){
     return $this->model::select($selected)
        ->where($conditions)->count();
    }

    public function sum($conditions , string $field_name ,array $selected = ["*"] ){
        return $this->model::select($selected)
           ->where($conditions)->sum( $field_name);
       }



}
