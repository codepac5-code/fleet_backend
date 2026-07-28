<?php
namespace App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order;

use App\Events\Panel\OrderBoardUpdated;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\RedisModel;
use App\Http\Services\Panel\Bookings\Logic\LiveTripPresenter;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\SubService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

abstract class OrderRedisModel  {

    /**
     * The active country's Redis namespace (db-name based, e.g. `fleet_sy:`), so
     * the order board is isolated per country — SY orders never land in the key
     * QA reads. Empty (no prefix) outside a shard context, preserving the legacy
     * single-country behavior.
     */
    protected static function shardPrefix(): string
    {
        $key = \App\Http\Core\GeoServices\ShardManager::shardKey();

        return $key !== '' ? $key . ':' : '';
    }

    protected static function orderKey($orderId): string
    {
        return self::shardPrefix() . OrderRedisKeies::ORDER->generateKey(['orderId' => $orderId]);
    }

    protected static function statusKey($status): string
    {
        return self::shardPrefix() . OrderRedisKeies::ORDER_STATUS->generateKey(['status' => $status]);
    }

    public static function store(Booking $order  ): void
    {
        $order_key = self::orderKey($order->id);
        $order_status_key = self::statusKey($order->status);
        Redis::set($order_key, serialize($order));
        Redis::sadd($order_status_key , $order->id);
    }

    public static function storeWithPagenationService(Booking $order): void
    {

        if ($order->driverId != null) {
            $driver = Driver::select(['firstName', 'lastName', 'photo', 'vehicleId','phoneNumber'])
                ->where(['id' => $order->driverId])
                ->with('vehicle')
                ->first();

                $order->driver = $driver;
  // $value =
        // [
        //     "id"=> $order->id,
        //     "startAt"=> $order->startAt,
        //     "endAt"=> $order->endAt,
        //     "amount"=> $order->amount,
        //     "discount"=> $order->discount,
        //     "time"=> $order->time,
        //     "totalAmount"=> $order->totalAmount,
        //     "rating"=> $order->rating,
        //     "reason"=> $order->reason,
        //     "couponId"   =>       $order->couponId,
        //     "status" =>             $order->status,
        //     "startAddress" =>       $order->startAddress,
        //     "endAddress" =>       $order->endAddress,
        //     "startLatitude" =>       $order->startLatitude,
        //     "startLongitude" =>       $order->startLongitude,
        //     "endLatitude" =>       $order->endLatitude,
        //     "endLongitude" =>       $order->endLongitude,
        //     "distance" =>       $order->distance,
        //     "paymentId" =>          $order->paymentId,
        //     "durationDiff" =>       $order->durationDiff,
        //     "userId"=>              $order->userId,
        //     "subServiceId"=>       $order->subServiceId,
        //     "multiDestnationArray"=>       $order->multiDestnationArray,
        //     "officeCommissionValue"=>       $order->officeCommissionValue,
        //     "driverCommissionValue"=>       $order->driverCommissionValue,
        //     "fleetCommissionValue"=>       $order->fleetCommissionValue,
        //     "driverCommissionPercentage"=>       $order->driverCommissionPercentage,
        //     "officeCommissionPercentage"=>       $order->officeCommissionPercentage,
        //     "fleetCommissionPercentage"=>       $order->fleetCommissionPercentage,
        //     "paymentStatus" =>       $order->paymentStatus,
        //     "PaymentDatetime"=>       $order->PaymentDatetime,
        //     "otherPaymentTransactionDetail"=>    $order->otherPaymentTransactionDetail,
        //     "created_at"=>       $order->created_at,
        // ];


            // $value['driver'] = [
            //     'firstName'=> $driver->firstName,
            //     'lastName'=>$driver->lastName,
            //     'photo'=>$driver->photo,
            //     'phoneNumber'=> $driver->phoneNumber,
            //     'vehicle' =>[
            //         'plate'=>$driver->vehicle->plate,
            //         'vehicleBrand'=>$driver->vehicle->vehicleBrand,
            //     ],
            // ];

        }

        $user = User::select(['firstName','lastName','photo',
        'phoneNumber'])->find($order->userId);

        $order->user = $user;
        // $value['user'] = [
        //     'firstName'=> $user->firstName,
        //     'lastName'=>$user->lastName,
        //     'photo'=>$user->photo,
        //     'phoneNumber'=> $user->phoneNumber
        // ];

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
        $subService = $sub_service->find($order->subServiceId);

        $order->subService = $subService;
        // $value['subService'] = ['name'=> $subService->name];

        if($order->multiDestnationArray != null){
            $multiDestnationArray = json_decode($order->multiDestnationArray);
            $order->multiDestnationArray = $multiDestnationArray;
            // $value['multiDestnationArray'] =  $multiDestnationArray;
        }

        if($order->officeId != null){
            $order->withOffice = true;
            $order->officeName = $order->office->officeName;

            // $value['withOffice'] = true;
            // $value['officeName'] = $order->office->officeName;

        }
        else {
            $order->withOffice = false;
            // $value['withOffice'] = false;
        }

        $order_key = self::orderKey($order->id);
        $order_status_key = self::statusKey($order->status);

        // $score = $order->created_at ? strtotime($order->created_at) : time();
        $score = $order->id;
        Redis::zadd($order_status_key, $score, $order->id);

        if ($order->status === OrderStatus::$Completed) {
            Redis::expire($order_key, 86400);
            $expiry = 86400;
            Redis::setex($order_key, $expiry, serialize($order)); //json_encode($value)
        }
        elseif($order->status === OrderStatus::$Pending )
        {
            $expiry = 60*60;
            Redis::setex($order_key ,$expiry, serialize($order) );
        }
        else {
            Redis::set($order_key , serialize($order) ); //json_encode($value)
        }

        self::pushBoard($order, 'upsert');

        // Redis::set($order_key, serialize($order));
    }

    protected static function pushBoard($order, string $action): void
    {
        try {
            if (! config('services.realtime.order_board')) {
                return;
            }

            if (! empty($order->is_scheduled)) {
                return;
            }

            $payload  = LiveTripPresenter::fromOrder($order);
            $channels = [self::shardPrefix() . 'panel-orders-admins'];

            if (! empty($order->officeId)) {
                $channels[] = self::shardPrefix() . 'panel-orders-office-' . $order->officeId;
            }

            event(new OrderBoardUpdated($channels, $action, $payload));
        } catch (\Throwable $e) {
            Log::warning('OrderBoard broadcast failed: ' . $e->getMessage());
        }
    }

    protected static function pushBoardRemove($orderId, $officeId): void
    {
        try {
            if (! config('services.realtime.order_board')) {
                return;
            }

            $payload  = ['id' => (int) $orderId];
            $channels = [self::shardPrefix() . 'panel-orders-admins'];

            if (! empty($officeId)) {
                $channels[] = self::shardPrefix() . 'panel-orders-office-' . $officeId;
            }

            event(new OrderBoardUpdated($channels, 'remove', $payload));
        } catch (\Throwable $e) {
            Log::warning('OrderBoard remove broadcast failed: ' . $e->getMessage());
        }
    }


    public static function storeCancelOrderId($orderId): void
    {
        $key = 'cancelled-orderIds';
        $expireAfterSeconds = 90 * 60;
        $score = time() + $expireAfterSeconds;
        Redis::zadd($key, [$orderId => $score]);
    }

    public static function getCancelOrderIds(): array
    {
        $key = 'cancelled-orderIds';
        $now = time();

        Redis::zremrangebyscore($key, '-inf', $now - 1);

        return Redis::zrange($key, 0, -1);
    }




    public static function getOrder(int $orderId)
    {
        $order_key = self::orderKey($orderId);
        $data = Redis::get($order_key);
        return $data ? unserialize($data) : null; //json_decode($data)
    }

    public static function delete( $orderId , $status ): void
    {
        $cached = self::getOrder($orderId);

        $order_key = self::orderKey($orderId);
        Redis::del($order_key);
        $order_status_key = self::statusKey($status);
        Redis::zrem($order_status_key, $orderId);

        if($status == OrderStatus::$Pending){
            OrderRedisModel::storeCancelOrderId($orderId);
        }

        if ($cached && empty($cached->is_scheduled)) {
            self::pushBoardRemove($orderId, $cached->officeId ?? null);
        }
    }

    public static function deleteCompletely(int $orderId, string $status): void
    {
        $status_key = self::statusKey($status);

        $order_key = self::orderKey($orderId);

        // Redis::zrem($status_key, $orderId);

        if (Redis::type($status_key) !== 'zset') {
            Redis::del($status_key);
        }
        Redis::zrem($status_key, $orderId);

        RedisManagerData::delete($order_key);
    }



    public static function get_status_count($status): int
    {
    $status_key = self::statusKey($status);
    $count = Redis::zcard($status_key);

    return $count;
    }


    public static function getByStatus(string $status)
    {
        $order_status_key = self::statusKey($status);
        $ids = Redis::smembers($order_status_key);
        // return collect($ids)->map(fn($id) => self::getOrder($id))->filter();
        return collect($ids)->map(function ($id) use ($order_status_key) {
            $order = self::getOrder($id);
            if (!$order) {
                Redis::srem($order_status_key, $id);
                return null;
            }
            return $order;
        })->filter();
    }

    public static function getByStatusAfterId(string $status, int $afterId)
{
    $order_status_key = self::statusKey($status);

    $ids = Redis::zrevrangebyscore($order_status_key, '+inf', "($afterId");

    return collect($ids)->map(function ($id) use ($order_status_key) {
        $order = self::getOrder($id);
        if (!$order) {
            Redis::zrem($order_status_key, $id);
            return null;
        }
        return $order;
    })->filter();
}

    public static function getByStatusPaginated(string $status, int $offset = 0, int $limit = 20){
    $order_status_key = self::statusKey($status);

    $ids = Redis::zrevrange($order_status_key, $offset, $offset + $limit - 1);

    //Redis::zrange($order_status_key, $offset, $offset + $limit - 1);
    //Redis::zrevrange($order_status_key, $offset, $offset + $limit - 1);

    return collect($ids)->map(function ($id) use ($order_status_key) {
        $order = self::getOrder($id);
        if (!$order) {
            Redis::zrem($order_status_key, $id);
            return null;
        }
        return $order;
    })->filter();
}


    // public static function updateStatus(Booking $order , string $oldStatus): void
    // {
    //     $order_status_key = self::statusKey($oldStatus);
    //     Redis::srem($order_status_key, $order->id);
    //     self::store($order);
    // }


    public static function updateStatus(Booking $order, string $oldStatus, string $newStatus): void {
        $old_status_key = self::statusKey($oldStatus);

        Redis::zrem($old_status_key, $order->id);

        $order->status = $newStatus;

        self::storeWithPagenationService($order);

        if ($newStatus === OrderStatus::$Completed) {
            Redis::expire(self::orderKey($order->id), 86400);
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
