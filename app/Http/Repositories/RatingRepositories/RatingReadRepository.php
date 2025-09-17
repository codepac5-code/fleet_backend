<?php
namespace App\Http\Repositories\RatingRepositories;

use App\Http\Core\Const\Options\Roles;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Driver;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RatingReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Rating();
    }

    public function dataTableUserRatings( ){

        $query = $this->model->query()->where(['rater_type'=>get_class(new User())]);

        if (Auth::guard('admin')->check()) {
            return $query->withTrashed();
        }

        else if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('officeId', $office->id)->withTrashed();
        }

        else if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->officeId) {
                return $query->where('officeId', $employee->officeId)->withTrashed();
            } else {
                return $query->withTrashed();
            }
        }        
        // if($auth->hasAnyRole(['super-admin'])){
        //     $query = $this->model->query()->where(['rater_type'=>get_class(new User())]);

        // }
        // elseif($auth->hasAnyRole(['office'])){
        //     $query = $this->model->query()->where(['officeId'=>$auth->id , 'rater_type'=>get_class(new User())]);
        // }

        // if ($filter != null) {
        //     if (isset($filter['column_status'])) {
        //         $query->where('isConected', $filter['column_status']);
        //     }
        // }

       return $query->orderBy('created_at','desc');
    }


    public function dataTableDriverRatings( ){

        $auth = auth()->user();


        $query = $this->model->query()->where(['rater_type'=>get_class(new Driver())]);

        if (Auth::guard('admin')->check()) {
            return $query->withTrashed();
        }

        else if (Auth::guard('office')->check()) {
            $office = Auth::guard('office')->user();
            return $query->where('officeId', $office->id)->withTrashed();
        }

        else if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            if ($employee->officeId) {
                return $query->where('officeId', $employee->officeId)->withTrashed();
            } else {
                return $query->withTrashed();
            }
        }
        
        // if($auth->hasAnyRole([Roles::Super_Admin])){
        //     $query = $this->model->query()->where(['rater_type'=>get_class(new Driver())]);

        // }
        // elseif($auth->hasAnyRole([Roles::Office])){
        //     $query = $this->model->query()->where(['officeId'=>$auth->id , 'rater_type'=>get_class(new Driver())]);
        // }

        // if ($filter != null) {
        //     if (isset($filter['column_status'])) {
        //         $query->where('isConected', $filter['column_status']);
        //     }
        // }

       return $query->orderBy('created_at','desc');
    }

}