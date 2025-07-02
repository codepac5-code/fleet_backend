<?php
namespace App\Http\Services\Driver\AcceptOrder\Logic;

use Carbon\Carbon;
use App\Http\Core\Classes\CommissionManagement;
use App\Http\Core\Classes\Operations\FleetSystemOperationGo;
use App\Http\Core\Classes\RedisManagerData;
use App\Http\Core\Classes\StatisticsEvent;
use App\Jobs\HandelRedisEvents;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\BalanceStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\OfficeWallet\OfficeWalletModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\OfficeWallet\OfficeWalletRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;

class AcceptOrderLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private AcceptOrderInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {
        
        $orderId = $this->input->getOrderId();

        
        $order = $this->repository->BookingRepository()->readRepository()
        ->find($orderId);


        if($order->driverId != null && $order->driverId == $this->input->getDriverId() ){
             return $response  = (new AcceptOrderOutput(['accept'  => true, 'orderModel' =>$this->order_info($order) ]  , 'order accepted'))
             ->send_as_object();
        }

        $driver = getAuthUser();

        if($driver->walletBalance < 5000){
            make_exception(__('messages.insufficient_balance_accept_order', ['amount' => 5000]));
        }

        //order_not_accepted($orderId)
        if( RedisManagerData::OrderNotAccepted($orderId) && $order->driverId == null){

        beginTransaction();
        
        $data = [
            'driverId'  => $this->input->getDriverId(),
            'status'    => OrderStatus::$InProgress ,
            'startAt'   => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        $this->commission_calculation( $order , $driver);

        $order_updated = $this->repository->BookingRepository()->updateRepository()->update(
                    ['id'=>$orderId],
                    $data
                );
            

        if($order_updated == 0){
                rollbackTransaction();
                //Make_Driver_online($driver->id);
                make_exception('can\'t update order');
            }
            //order.41:notAcceptedByDriver
            $r_data =  RedisManagerData::getOrderDetails($orderId); 
            //get_order_details_from_redis($orderId);

            $driverIds = $r_data['driverIds'];


            $idToRemove = $this->input->getDriverId();
            
            $driverIds = $this->removeDriverId($driverIds, $idToRemove);

            foreach($driverIds as $driverId){
                if($driverId != $this->input->getDriverId()){
                    dispatch(new HandelRedisEvents('delete_order', [
                        'driverId' => $driverId,
                        'orderId' => $orderId,
                    ]));
                }
            }    
        
        
        // array_map(function ($driverId) use ($orderId) {
        //         dispatch(new HandelRedisEvents('delete_order', [
        //             'driverId' => $driverId,
        //             'orderId' => $orderId,
        //         ]));
        // }, $driverIds);
          
        //--------
        if ( RedisManagerData::OrderNotAccepted($orderId)){
                RedisManagerData::deleteOrderDetails($orderId);
                RedisManagerData::AcceptOrder($orderId);
                $this->redisOrderDatabaseModel($orderId);
                //delete_order_details_from_redis($orderId);
                make_Driver_offline($driver->id);
                $driverUpdateRepository = $this->repository->DriverRepository()->updateRepository();
                $driverUpdateRepository->update(['id'=>$this->input->getDriverId()] , [
                    'isConected' => false
                ]);

                
                $new_counts = FleetSystemOperationGo::move_orders_from_pinding_to_ongoing(1); 

                StatisticsEvent::Pending_Ride
                ->send_event_to_admin(
                    $new_counts->getFromValue()
                );

                StatisticsEvent::Ongoing_Ride
                ->send_event_to_admin(
                    $new_counts->getToValue()
                );

                $new_pending_amount = FleetSystemOperationGo::add_moeny_to_pending_amount(5000); 
                StatisticsEvent::Pending_Card
                ->send_event_to_admin(
                    $new_pending_amount
                );


                // FleetSystemOperationGo::move_moeny
                // if(!$driver->fleetDriver){
                //     OfficeOperationGo::move_orders_from_pinding_to_ongoing($driver->officeId , 1);
                // }

                // $fleet_info = $this->repository->FleetOfficeRepository()->readRepository()->getFirstByConditions([]);


                // $statistic = $this->repository->FleetStatisticRepository()->readRepository()->getFirstByConditions([]);
                // $new_pending_amount = $statistic->pending_amount + ($order->totalAmount * 0.1);

                
                // if($updated > 0){return 0;}

                
                //-------


                  


                  //----------------

                // }

   
                commitTransaction();
                return $response  = (new AcceptOrderOutput(['accept'  => true ,
                'orderModel' =>$this->order_info($order) ]  , 'order accepted'))->send_as_object();

        } 
            rollbackTransaction();
            $response  = new AcceptOrderOutput(['accept'  => false ]  , 'order taked by another driver');
            return $response->send_as_object();
        }

        $response  = new AcceptOrderOutput(['accept' =>  false ] , 'order taked by another driver');
        return $response->send_as_object();
   }


   

    function removeDriverId(array $driverIds, $idToRemove) {
                $driverIds = array_filter($driverIds, function ($driverId) use ($idToRemove) {
                    return $driverId !== $idToRemove;
                });
                $driverIds = array_values($driverIds);
                return $driverIds;
    }


    public function redisOrderDatabaseModel($orderId){
        $order = $this->repository->BookingRepository()->readRepository()
        ->find($orderId);

        // update order status
        OrderRedisModel::updateStatus($order,
            OrderStatus::$Pending,
            OrderStatus::$OnGoing
        );
    }


    public function order_info($order){
        // return 'dd';
        $select = select_by_language([
            'name',
            'id'
            
             ] , [
                'name_en as name',
                'id'
        ]);

        $payment_name = $this->repository->PaymentMethodRepository()
        ->readRepository()
        ->getFirstByConditions(['id'=> $order->paymentId] ,$select )->name;


        $select = select_by_language([
            'id',
            'name',
            'image',
            'status',
            'description',
            'openPrice',
            'kmPrice',
            'minutePrice',
            'serviceId',
             ] , [
                'id',
                'image',
                'status',
                'openPrice',
                'kmPrice',
                'minutePrice',
                'serviceId',
                'name_en as name',
                'description_en as description'
        ]);

        $sub_service = $this->repository
        ->SubServiceRepository()->readRepository()
        ->getFirstByConditions(['id' => $order->subServiceId ] ,$select);


        $user = $this->repository->UserRepository()
        ->readRepository()->getByValue('id', $order->userId);


    return  [ 
        "startAddress" => $order->startAddress,
        "startLatitude" => (double)$order->startLatitude,
        "startLongitude" => (double)$order->startLongitude,
        "endAddress" => $order->endAddress,
        "endLatitude" => (double)$order->endLatitude,
        "endLongitude" => (double)$order->endLongitude,
        "distance" => (double)$order->distance,
        "time"          =>(string) $order->time,
        "totalAmount" => (int) $order->totalAmount,
        "amount" => (int) $order->amount,
        'subService' => $sub_service->name,
        'paymentMethod' =>(string) $payment_name,
        'orderId' => $order->id,
        'firstName' => $user->firstName,
        'lastName' => $user->lastName,
        'phoneNumber' => $user->phoneNumber,
        'officePhone'=>'0935501111',
        'waypoints' => json_decode($order->multiDestnationArray),
        'kmPrice' =>(double) $sub_service->kmPrice,
        'minutePrice' =>(double) $sub_service->minutePrice,
        'openPrice' => (double)$sub_service->openPrice ,
        ];
         
    }


    public function commission_calculation($order , $driver){

        $order_after_commission = CommissionManagement::OrderCommissionCalculation( $order , $driver);
        FleetWalletRedisModel::addBalanceValueByBalanceStatus( BalanceStatus::$Pending , $order_after_commission->fleetCommissionValue );
        if($order_after_commission->officeId != null && $order_after_commission->officeCommissionValue > 0  ){
            OfficeWalletRedisModel::addBalanceValueByBalanceStatus($order_after_commission->officeId, BalanceStatus::$Pending , $order_after_commission->fleetCommissionValue);
        }
    }



}

        // $this->repository->BookingRepository()->readRepository()->
            
        // "startAddress": ,
        // "startLatitude": ,
        // "startLongitude": ,
        // "endAddress": ,
        // "endLatitude": ,
        // "endLongitude": ,
        // "distance": ,
        // "time": ,
        // "totalAmount": ,
        // "amount": ,
        // 'subService': ,
        // 'paymentMethod': ,
        // 'orderId': ,
        // 'firstName':   ,
        // 'lastName':   ,
        // 'phoneNumber': ,
    




        

    