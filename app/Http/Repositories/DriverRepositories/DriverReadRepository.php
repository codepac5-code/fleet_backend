<?php
namespace App\Http\Repositories\DriverRepositories;

use App\Models\Driver;
use Illuminate\Support\Facades\Log;
use App\Notifications\PrivateNotification;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Support\Facades\Notification;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;

class DriverReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new Driver();
    }



    public function notifyByConditions( $conditions , NotificationModel $notificationModel , array $selected = ["id"] ) {
        $user = $this->model->select($selected)
        ->chunk(500, function ($users) use ($notificationModel) {
                try {
                    Notification::sendNow($users, new PrivateNotification($notificationModel));
                } catch (\Exception $e) {
                    Log::error('Chunk failed to send notifications', ['error' => $e->getMessage()]);
                }
            });
        return $user;
    }

    public function getNotifications( $id , $paginate = 15 ,array $selected = ["*"] ) {
        return $this->model->select($selected)->find($id)->notifications()->paginate($paginate);
    }

    public function notifyDriver( $id , NotificationModel $notificationModel , array $selected = ["*"] ) {
        return $this->model->select($selected)->find($id)->notify(new PrivateNotification($notificationModel));
    }

    public function dataTableDriver( $filter){

        $auth = auth()->user();
        if($auth->hasAnyRole(['super-admin'])){
            $query = Driver::query();
        }
        elseif($auth->hasAnyRole(['office'])){
            $query = Driver::query()->where(['officeId'=>$auth->id]);
        }

        if ($filter != null) {
            if (isset($filter['column_status'])) {
                $query->where('isConected', $filter['column_status']);
            }
        }

       return $query->orderBy('created_at','desc');
    }
}
