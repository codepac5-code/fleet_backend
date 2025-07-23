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
        $user= $this->model->select($selected)->find($id);
         $notifications = $user->notifications()->paginate($paginate);
         $user->unreadNotifications->markAsRead();
        return $notifications;
    }

    public function notifyDriver( $id , NotificationModel $notificationModel , array $selected = ["*"] ) {
        return $this->model->select($selected)->find($id)->notify(new PrivateNotification($notificationModel));
    }

    public function dataTableDriver( $filter){

        $query = $this->model->scopeForCurrentUser();
        
   

        if ($filter != null) {
            if (isset($filter['column_status'])) {
                $query->where('isConected', $filter['column_status']);
            }
        }

       return $query->orderBy('created_at','desc');
    }
}
