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
             return $response  = (new AcceptOrderOutput(['accept'  => true ]  , 'order accepted'))->send_as_object();
        }


        //order_not_accepted($orderId)
        if( RedisManagerData::OrderNotAccepted($orderId) && $order->driverId == null){
        $driver = getAuthUser();

        beginTransaction();
        
        $data = [
            'driverId'  => $this->input->getDriverId(),
            'status'    => OrderStatus::$InProgress ,
            'startAt'   => Carbon::now()->format('Y-m-d H:i:s'),
        ];

        CommissionManagement::OrderCommissionCalculation( $order , $driver);

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
                commitTransaction();
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
                return $response  = (new AcceptOrderOutput(['accept'  => true ]  , 'order accepted'))->send_as_object();

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