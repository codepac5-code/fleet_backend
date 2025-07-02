<?php
namespace App\Http\Repositories\UserRepositories;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Notifications\PrivateNotification;
use App\Http\Core\Models\NotificationModel;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BroadcastUserNotification;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\ReadRepository;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\UserNotification_model;

class UserReadRepository extends ReadRepository
{
    public function __construct()
    {
        $this->model = new User();
    }

  
    
    public function addCouponToUser( 
        $userId,
        $percentage_discount,
        $expireDate,
        $prefix = 'FLEET-',
        $limit = 1 ,
        $length = 10 ):string {


        $bytes = random_bytes(ceil($length / 2)); 
        $random = strtoupper(bin2hex($bytes)); 
        $coupon_code = $prefix . '-' . substr($random, 0, $length);
         
        $coupon = Coupon::create([
            'code' =>  $coupon_code,
            'discounType' => 'percentage',
            'discount' => $percentage_discount / 100,
            'expireDate' => $expireDate,
            'isActive' => true,
            'limit'=> $limit,
        ]);

        $user = $this->model->find($userId);

        if($user == null){
            make_exception('user not exists , please check user id!');
        }

        if($coupon == null){
            make_exception(__('messages.something_wrong'));
        }

        CouponUser::create(['couponId'=>$coupon->id, 'userId'=>$user->id, 'count'=>0]);

        return $coupon_code;
    }

    public function getByConditions( $conditions=[] , array $selected = ["*"] ):array {
        $user = $this->model->select($selected)
        ->where($conditions)->get();
        return $user;
    }

    public function getFirstByConditions( $conditions , array $selected = ["*"] ) {
        $user = $this->model->select($selected)
        ->where($conditions)->first();
        return $user;
    }



    public function find(int $id , array $selected = ["*"]){
        return $this->model->select($selected)->find($id);
    }
    // public function getAll(array $selected = ["*"]){return null;}

    public function getById(int $id , array $selected = ["*"]){return null;}


    
    public function notifyByConditions( $conditions =[] , NotificationModel $notificationModel , array $selected = ["id"] ) {
        $user = UserNotification_model::select($selected)->where($conditions)
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


    public function notifyUser(int $id , NotificationModel $notificationModel , array $selected = ["*"] ) {
        return$this->model->select($selected)->find($id)->notify(new PrivateNotification($notificationModel));
    }

    // public function notifyAllUser(  NotificationModel $notificationModel ) {
    //     Notification::sendNow($users, new BroadcastUserNotification($notificationModel));
    // }

    
    


    

    // use Illuminate\Support\LazyCollection;

// LazyCollection::make(function () {
//     yield from User::cursor();
// })->chunk(100)->each(function ($users) use ($notificationData) {
//     Notification::sendNow($users, new GlobalNotification($notificationData));
// });





// User::select('id')->chunk(100, function ($users) use ($notificationData) {
//     Notification::send($users, new GlobalNotification($notificationData));
// });



// User::chunk(100, function ($users) use ($notificationData) {
//     try {
//         Notification::send($users, new GlobalNotification($notificationData));
//     } catch (\Exception $e) {
//         \Log::error('Chunk failed to send notifications', ['error' => $e->getMessage()]);
//     }
// });
}
