<?php
namespace App\Http\Services\User\CancelOrder\Logic;

use App\Events\DeleteOrder;
use App\Http\Core\Classes\Operations\FleetSystemOperationGo;
use App\Http\Core\Classes\RedisManager;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Classes\StatisticsEvent;
use App\Http\Core\Const\Options\OrderStatus;
use App\Jobs\HandelRedisEvents;
use Illuminate\Support\Facades\Redis;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;

class CancelOrderLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CancelOrderInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {
        
        $orderId = $this->input->getOrderId();

        // $key = 'order.'.$orderId.':notAcceptedByDriver';      

        if(RedisManagerData::OrderNotAccepted($orderId)){
            $r_data = RedisManagerData::getOrderDetails($orderId);     
            $driverIds = $r_data ['driverIds'];
        
            foreach($driverIds as $driverId) {
                event((new DeleteOrder($orderId, $driverId)));
                // dispatch(new HandelRedisEvents('delete_order',[
                //     'orderId' => $orderId,
                //     'driverId' =>$driverId,
                // ]));
            }
            RedisManagerData::AcceptOrder($orderId);

        }

        
        // $new_count = FleetSystemOperationGo::add_orders_to_pinding_rides(-1);
        // StatisticsEvent::Pending_Ride->send_event_to_admin($new_count);
        $this->updateRedisDatabase_Order();
        
        $response  = new CancelOrderOutput([] , __('messages.order_cancelled'));
        return $response->send_as_array();
   }


   private function updateRedisDatabase_Order(){
    OrderRedisModel::delete($this->input->getOrderId() ,
            OrderStatus::$Pending
        );
   }
}
