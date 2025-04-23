<?php
namespace App\Http\Repositories\RatingUserRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\RatingUser;
use App\Models\User;

class RatingUserReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new RatingUser();
    }



    public function dataTableUserRatings( ){

        $auth = auth()->user();
        
        if($auth->hasAnyRole(['super-admin'])){
            $query = RatingUser::query()->where(['rater_type'=>get_class(new User())]);

        }
        elseif($auth->hasAnyRole(['office'])){
            $query = RatingUser::query()->where(['officeId'=>$auth->id , 'rater_type'=>get_class(new User())]);
        }

        // if ($filter != null) {
        //     if (isset($filter['column_status'])) {
        //         $query->where('isConected', $filter['column_status']);
        //     }
        // }

       return $query->orderBy('created_at','desc');
    }

    

}