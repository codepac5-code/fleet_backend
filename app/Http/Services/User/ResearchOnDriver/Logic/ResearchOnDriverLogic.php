<?php
namespace App\Http\Services\User\ResearchOnDriver\Logic;

use App\Events\SearchOnDriver;
use App\Http\Core\Classes\RedisManagerData;
use App\Jobs\HandelRedisEvents;
use App\Jobs\SearchOnDriverJob;
use Illuminate\Support\Facades\Redis;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Redis\RedisManager;

class ResearchOnDriverLogic implements Service {


    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ResearchOnDriverInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }



    public function execute (): ResponseModel {

        $r = 0.2;
        $highest_radius = 2;
        // $key = 'order.'.$this->input->getOrderId().':notAcceptedByDriver';
        $orderId = $this->input->getOrderId();
        if(RedisManagerData::OrderNotAccepted($orderId)){
        beginTransaction();
        $order = $this->repository->BookingRepository()->readRepository()->find(
            $this->input->getOrderId()
        );

        if($order == null ){
            make_exception(__('messages.order_not_found'));
        }
        

        $sub_service = $this->repository->SubServiceRepository()->readRepository()->find(
            $order->subServiceId
        );

        $payment_methods = $this->repository->PaymentMethodRepository()->readRepository()
        ->getByValue('id',$order->paymentId);
        
        $multiDestinations = json_decode($order->multiDestnationArray, true);

        $data = [
            'startAddress'          =>$order->startAddress,
            'endAddress'            =>$order->endAddress,
            'time'                  =>$order->durationDiff,
            'startLatitude'         =>$order->startLatitude,
            'startLongitude'        =>$order->startLongitude,
            'endLatitude'           =>$order->endLatitude,
            'endLongitude'          =>$order->endLongitude,
            'distance'              => (float)$order->distance,
            'couponCode'=> $order->couponId,
            'subService'=>$sub_service->name,
            'subServiceId'=>$sub_service->id,
            'userId'=>$order->userId,
            'orderId'=>$order->id,
            'paymentMethod'=>$payment_methods->name,
            'totalAmount'=>$order->totalAmount,
            'amount'=>$order->amount,
            'waypoints' => $multiDestinations ,
            'radius'=> 1
    ];

        if( RedisManagerData::OrderNotAccepted($orderId)){ 
            $order_info = RedisManagerData::getOrderDetails($orderId);
            $new_radius = $order_info['radius'] + $r;
            if( $new_radius < $highest_radius ){ 
                $order_info['radius']  = $order_info['radius'] + $r;
                RedisManagerData::storeOrderDetails( $orderId , $order_info , 1800 );
                $data['radius'] = $new_radius;
            }
 
            info('--------- research on driver  orderId:'.$this->input->getOrderId());
            SearchOnDriverJob::dispatch($data)->onQueue('jobs');

           // dispatch(new HandelRedisEvents('research_on_driver',$data));
            $response  = new ResearchOnDriverOutput([] , __('messages.search_scope_expanded', ['radius' => $data['radius']])    );
            return $response->send_as_array();
        }
    }
        make_exception(__('messages.order_not_found'));
   }
}