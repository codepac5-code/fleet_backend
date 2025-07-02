<?php
namespace App\Http\Services\User\MakeOrder\Logic;
use Error;
use App\Events\SearchOnDriver;
use App\Http\Core\Classes\Operations\FleetSystemOperationGo;
use App\Http\Core\Classes\RedisManager;
use App\Http\Core\Classes\StatisticsEvent;
use App\Jobs\HandelRedisEvents;
use App\Jobs\SearchOnDriverJob;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\BalanceStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Jobs\FollowOrder\MakePendingOrderCardJob;
use App\Jobs\FollowOrder\PendingOrder;
use App\Models\Booking;

class MakeOrderLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private MakeOrderInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {


        $booking_c_repo = $this->repository->BookingRepository()->createRepository();
        $coupon_r_repo  = $this->repository->CouponRepository()->readRepository();

        $sub_service = $this->repository->SubServiceRepository()->readRepository();
        $payment_methods = $this->repository->PaymentMethodRepository()->readRepository()   
        ->getByValue('id',$this->input->getPaymentId());


        // ------------------

        // $open_price       = $sub_service->openPrice;
        // $kmPrice          = $sub_service->kmPrice;
        // $minutePrice      = $sub_service->minutePrice;

        // //------------------
        // $distance  = $this->input->getDistance();
        // $time = $this->input->getTime();

        // //------------------

        //// open_price + (km-price * km) + (min-price * min)
        // $total_amount   = $open_price +( $distance * $kmPrice ) + ($minutePrice * $time);
        // $this->input->setTotalAmount( $total_amount);

        // //------------------

        $order_data = $this->input->bookingData();

        beginTransaction();

        if($this->input->getCouponCode() != null ){
            $coupon = $coupon_r_repo->getByValue('code' , $this->input->getCouponCode());
            $order_data['couponId'] = $coupon->id;
            $order_data['discount'] = $coupon->discount;
        }
        // create order..
        $order = $booking_c_repo->create($order_data);

        $sub_service = $sub_service->getByValue('id',$this->input->getSubServiceId());
        if($order == null || $sub_service == null ){
            rollbackTransaction();
            make_exception(__('messages.something_wrong'));
            //make_exception(ErrorMessages::getKey(ErrorMessages::$SomeThingWentWrong));
        }


        $data = [
                'startAddress'          =>$this->input->getStartAddress(),
                'endAddress'            =>$this->input->getEndAddress(),
                'time'                  =>$this->input->getTime(),
                'startLatitude'         =>$this->input->getStartLatitude(),
                'startLongitude'        =>$this->input->getStartLongitude(),
                'endLatitude'           =>$this->input->getEndLatitude(),
                'endLongitude'          =>$this->input->getEndLongitude(),
                'distance'              =>$this->input->getDistance(),
                'couponCode'=>$this->input->getCouponCode(),
                'subService'=>$sub_service->name,
                'subServiceId'=>$sub_service->id,
                'userId'=>$this->input->getUserId(),
                'orderId'=>$order->id,
                'paymentMethod'=>$payment_methods->name,
                'totalAmount'=>$order->totalAmount,
                'amount'=>$this->input->getAmount(),
                'waypoints' => $this->input->multiDestnationArray
        ];

        commitTransaction();
        SearchOnDriverJob::dispatch($data)
        ->onQueue('jobs');

        $this->redisOrderDatabaseModel($order->id);

        // MakePendingOrderCardJob::dispatch($order->id);
        
        
        $new_count = FleetSystemOperationGo::add_orders_to_pinding_rides(1);
        StatisticsEvent::Pending_Ride->send_event_to_admin($new_count);



        // $fleet_pending  = FleetSystemOperationGo::add_moeny_to_pending_amount();
        // $office_pending = FleetSystemOperationGo::add_moeny_to_pending_amount();



        // $redis_manager = new RedisManager();

        // $pending_rides = $redis_manager->get_system_daily_pending_rides();
        // $redis_manager->set_system_daily_pending_rides($pending_rides + 1);

        // StatisticsEvent::New_Order_Search
        // ->send_event_to_admin($pending_rides + 1);

        // $ammount = $redis_manager->add_to_system_pending_amount($order->totalAmount * 0.1);

        // StatisticsEvent::Pending_Card
        // ->send_event_to_admin($ammount);


        // dispatch(new HandelRedisEvents('research_on_driver',$data));

        // send response..
        $response  = new MakeOrderOutput([
            'orderId'=> $order->id ,
            'openPrice'=>$sub_service->openPrice,
            'kmPrice'=>$sub_service->kmPrice,
            'minutePrice'=>$sub_service->minutePrice,
            ] , __('messages.order_created'));
        return $response->send_as_array();
    }


   public function redisOrderDatabaseModel($orderId){
    $order = Booking::find($orderId);
    $order->status = OrderStatus::$Pending;
    OrderRedisModel::storeWithPagenationService($order);
   }


}