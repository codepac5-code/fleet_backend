<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order;

use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\RedisModel;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

abstract class OrderRedisModel  {

    public static function store(Booking $order  ): void
    {
        $order_key = OrderRedisKeies::ORDER->generateKey(['orderId'=>$order->id]);
        $order_status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status'=>$order->status]);
        Redis::set($order_key, serialize($order));
        Redis::sadd($order_status_key , $order->id);
    }

    public static function storeWithPagenationService(Booking $order): void
    {

        if ($order->driverId != null) {
            $order->driver = Driver::select(['firstName', 'lastName', 'photo', 'vehicleId'])
                ->where(['id' => $order->driverId])
                ->with('vehicle')  
                ->first(); 
        }
        
        $order->user = User::select(['firstName','lastName','photo',
        'phoneNumber'])->find($order->userId);
        
        switch(app()->getLocale())
        {
            case 'en': 
                $sub_service = SubService::select([
                    'name_en as name'
                ]);

            default :
                $sub_service = SubService::select([
                    'name'
              ]);
        }
        $order->subService = $sub_service->find($order->subServiceId);

        if($order->multiDestnationArray != null){
            $order->multiDestnationArray = json_decode($order->multiDestnationArray);
        }

        if($order->officeId != null){
            $order->withOffice = true;
            $order->officeName = $order->office->officeName;

        }else{
            $order->withOffice = false;
        }

        $order_key = OrderRedisKeies::ORDER->generateKey(['orderId' => $order->id]);
        $order_status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status' => $order->status]);
    
        // $score = $order->created_at ? strtotime($order->created_at) : time();
        $score = $order->id;

        Redis::zadd($order_status_key, $score, $order->id);
    
        Redis::set($order_key, serialize($order));
    
        if ($order->status === OrderStatus::$Completed) {
            Redis::expire($order_key, 86400);
        }
    }


    public static function get(int $orderId): ? Booking
    {
        $order_key = OrderRedisKeies::ORDER->generateKey(['orderId'=>$orderId]);
        $data = Redis::get($order_key);
        return $data ? unserialize($data) : null;
    }

    public static function delete( $orderId , $status ): void
    {
        $order_key = OrderRedisKeies::ORDER->generateKey(['orderId'=>$orderId]);
        Redis::del($order_key);
        $order_status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status'=>$status]);
        Redis::srem( $order_status_key , $orderId);
    }

    public static function deleteCompletely(int $orderId, string $status): void
    {
        $status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status' => $status]);

        $order_key = OrderRedisKeies::ORDER->generateKey(['orderId' => $orderId]);

        // Redis::zrem($status_key, $orderId);
        
        if (Redis::type($status_key) !== 'zset') {
            Redis::del($status_key);
        }
        Redis::zrem($status_key, $orderId);
        
        RedisManagerData::delete($order_key);
    }



    public static function get_status_count($status): int
    {
    $status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status' => $status]);
    $count = Redis::zcard($status_key);

    return $count;
    }


    public static function getByStatus(string $status)
    {
        $order_status_key = OrderRedisKeies::ORDER_STATUS->generateKey([ 'status'=> $status ]);
        $ids = Redis::smembers($order_status_key);
        // return collect($ids)->map(fn($id) => self::get($id))->filter();
        return collect($ids)->map(function ($id) use ($order_status_key) {
            $order = self::get($id);
            if (!$order) {
                Redis::srem($order_status_key, $id);
                return null;
            }
            return $order;
        })->filter();
    }

    public static function getByStatusAfterId(string $status, int $afterId)
{
    $order_status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status' => $status]);

    $ids = Redis::zrevrangebyscore($order_status_key, '+inf', "($afterId"); 

    return collect($ids)->map(function ($id) use ($order_status_key) {
        $order = self::get($id);
        if (!$order) {
            Redis::zrem($order_status_key, $id);
            return null;
        }
        return $order;
    })->filter();
}

    public static function getByStatusPaginated(string $status, int $offset = 0, int $limit = 20){
    $order_status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status' => $status]);

    $ids = Redis::zrevrange($order_status_key, $offset, $offset + $limit - 1);

    //Redis::zrange($order_status_key, $offset, $offset + $limit - 1);
    //Redis::zrevrange($order_status_key, $offset, $offset + $limit - 1);

    return collect($ids)->map(function ($id) use ($order_status_key) {
        $order = self::get($id);
        if (!$order) {
            Redis::zrem($order_status_key, $id); 
            return null;
        }
        return $order;
    })->filter();
}


    // public static function updateStatus(Booking $order , string $oldStatus): void
    // {
    //     $order_status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status'=>$oldStatus]);
    //     Redis::srem($order_status_key, $order->id);
    //     self::store($order);
    // }


    public static function updateStatus(Booking $order, string $oldStatus, string $newStatus): void {
        $old_status_key = OrderRedisKeies::ORDER_STATUS->generateKey(['status' => $oldStatus]);

        Redis::zrem($old_status_key, $order->id);

        $order->status = $newStatus;

        self::storeWithPagenationService($order); 

        if ($newStatus === OrderStatus::$Completed) {
            Redis::expire(OrderRedisKeies::ORDER->generateKey(['orderId' => $order->id]), 86400);
        }
    }



    // -------------- <<  Count  >> --------

    public static function add_new_pending_order(int|null $officeId = null , $plus = true)
    {
        // office
        if($officeId != null){

            $office_key = OrderRedisKeies::ORDER_PENDING_COUNT->generateKey(['officeId'=>$officeId]);
            $count = Redis::get($office_key);
            $office_order_count = $plus ? $count + 1 : $count - 1;
            Redis::set($office_key , $office_order_count);

        }
        // fleet
        $fleet_key  = OrderRedisKeies::ORDER_PENDING_COUNT->generateKey(['officeId' => 000]);

        $count = Redis::get($fleet_key)  + 1;
        $fleet_order_count  = $plus ? $count + 1 : $count - 1;

        Redis::set($fleet_key , $fleet_order_count);

    }


    public function move_order_from_pending_to_ongoing(int|null $officeId = null , $plus = true){
        $value = $plus ? 1 : -1;

        //------- office
        if($officeId != null){

            // from
            $office_key = OrderRedisKeies::ORDER_PENDING_COUNT->generateKey(['officeId'=>$officeId]);
            $count = Redis::get($office_key);
            $office_order_count =  $count - $value;
            Redis::set($office_key , $office_order_count);

            // to

            $office_key = OrderRedisKeies::ORDER_ONGOING_COUNT->generateKey(['officeId'=>$officeId]);
            $count = Redis::get($office_key);
            $office_order_count =  $count + $value;
            Redis::set($office_key , $office_order_count);

        }
        //--------- fleet

        //from
        $fleet_key  = OrderRedisKeies::ORDER_PENDING_COUNT->generateKey(['officeId' => 000]);
        $count = Redis::get($fleet_key)  + 1;
        $fleet_order_count  =  $count - $value ;
        Redis::set($fleet_key , $fleet_order_count);

        //to
        $fleet_key  = OrderRedisKeies::ORDER_ONGOING_COUNT->generateKey(['officeId' => 000]);
        $count      = Redis::get($fleet_key)  + 1;
        $fleet_order_count  = $count + $value ;
        Redis::set($fleet_key , $fleet_order_count);
    }

    public function move_order_from_ongoing_to_completed(int|null $officeId = null , $plus = true){
        $value = $plus ? 1 : -1;

        //------- office
        if($officeId != null){

            // from
            $office_key = OrderRedisKeies::ORDER_ONGOING_COUNT->generateKey(['officeId'=>$officeId]);
            $count = Redis::get($office_key);
            $office_order_count =  $count - $value;
            Redis::set($office_key , $office_order_count);
            // to

            $office_key = OrderRedisKeies::ORDER_COMPLETED_COUNT->generateKey(['officeId'=>$officeId]);
            $count = Redis::get($office_key);
            $office_order_count =  $count + $value;
            Redis::set($office_key , $office_order_count);

        }
        //--------- fleet

        //from
        $fleet_key  = OrderRedisKeies::ORDER_ONGOING_COUNT->generateKey(['officeId' => 000]);
        $count = Redis::get($fleet_key)  + 1;
        $fleet_order_count  =  $count - $value ;
        Redis::set($fleet_key , $fleet_order_count);

        //to
        $fleet_key  = OrderRedisKeies::ORDER_COMPLETED_COUNT->generateKey(['officeId' => 000]);
        $count      = Redis::get($fleet_key)  + 1;
        $fleet_order_count  = $count + $value ;
        Redis::set($fleet_key , $fleet_order_count);


    }



    //---------------------------------------------------------------

    public static function get_pending_count($officeId = null){

        if($officeId == null){ $officeId = 000 ;}
        $key  = OrderRedisKeies::ORDER_PENDING_COUNT->generateKey(['officeId' => $officeId]);
        return Redis::get($key);
    }

    // public static function add_pending_count($officeId = null , $plus = true){

    //     $value = $plus ? 1 : -1;
    //     if($officeId == null){
    //         $key  = OrderRedisKeies::ORDER_PENDING_COUNT->generateKey(['officeId' => $officeId]);
    //         $count =  Redis::get($key) + $value;
    //         Redis::set($key , $count);
    //     }
    //     $key  = OrderRedisKeies::ORDER_PENDING_COUNT->generateKey(['officeId' =>000]);
    //     $count =  Redis::get($key) + $value;
    //     Redis::set($key , $count);


    // }

    // ----------

    public static function get_completed_count($officeId = null){
        
        if($officeId == null){ $officeId = 000 ;}
        $key  = OrderRedisKeies::ORDER_COMPLETED_COUNT->generateKey(['officeId' => $officeId]);
        return Redis::get($key);
    }


    // public static function add_completed_count($officeId = null , $plus = true){

    //     $value = $plus ? 1 : -1;
    //     if($officeId != null){ 
    //         $key  = OrderRedisKeies::ORDER_COMPLETED_COUNT->generateKey(['officeId' => $officeId]);
    //         $count =  Redis::get($key) + $value;
    //         Redis::set($key , $count);
    //     }
    //     $key  = OrderRedisKeies::ORDER_COMPLETED_COUNT->generateKey(['officeId' => 000]);
    //     $count =  Redis::get($key) + $value;
    //     Redis::set($key , $count);
    // }

    //---------------

    public static function get_ongoing_count($officeId = null){
        
        if($officeId == null){ $officeId = 000 ;}
        $key  = OrderRedisKeies::ORDER_ONGOING_COUNT->generateKey(['officeId' => $officeId]);
        return Redis::get($key);
    }

    // public static function add_ongoing_count($officeId = null , $plus = true){

    //     $value = $plus ? 1 : -1;
    //     if($officeId == null){ 
    //         $key  = OrderRedisKeies::ORDER_ONGOING_COUNT->generateKey(['officeId' => $officeId]);
    //         $count =  Redis::get($key) + $value;
    //         Redis::set($key , $count);
    //     }
    //     $key  = OrderRedisKeies::ORDER_ONGOING_COUNT->generateKey(['officeId' => 000]);
    //     $count =  Redis::get($key) + $value;
    //     Redis::set($key , $count);
    // }

}