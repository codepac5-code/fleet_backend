<?php
namespace App\Http\Services\Driver\ReceiveCompletedOrderInfo\Logic;

use App\Http\Core\Const\Options\PaymentStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\BalanceStatus;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\FleetWallet\FleetWalletRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\OfficeWallet\OfficeWalletRedisModel;

class ReceiveCompletedOrderInfoLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ReceiveCompletedOrderInfoInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {


        $order = $this->repository->BookingRepository()
        ->readRepository()
        ->getByValue('id', $this->input->getOrderId());

        if($order == null){
            make_exception('order not found!');
        }


        $ride_price = $this->input->getPrice();
        if($ride_price > $order->amount){
            $fleetCommissionValue = ($order->fleetCommissionPercentage  /100) * $ride_price;

            if($order->couponId != null){
                $coupon = $order->coupon;
                if($coupon->isPercentage){
                    $discountAmount = $coupon->discount * $ride_price;
                }
                else {
                    $discountAmount = max( $ride_price - $coupon->discount, 0);
                }
                $fleetCommissionValue = max($fleetCommissionValue - $discountAmount , 0); 
                // $discountAmount = $coupon->discount * $ride_price;
            }        
            $driverCommissionValue =($order->driverCommissionPercentage /100) * $ride_price;
            $officeCommissionValue =  ($order->officeCommissionPercentage /100)  * $ride_price;

            $updated_order = $this->repository->BookingRepository()
            ->updateRepository()
            ->update(['id'=>$this->input->getOrderId()],[
                'time'            => $this->input->getTime(),
                'distance'        => $this->input->getDistance(),
                'amount'          => $ride_price,
                'totalAmount'     => $ride_price - $discountAmount,
                'paymentStatus'   => PaymentStatus::$Pending, 
                'driverCommissionValue'    => $driverCommissionValue,
                'officeCommissionValue'    => $officeCommissionValue,
                'fleetCommissionValue'     => $fleetCommissionValue,
            ]
        );
        if(!$updated_order > 0 ){
            make_exception(__('messages.something_wrong'));
        }


        FleetWalletRedisModel::addBalanceValueByBalanceStatus( BalanceStatus::$Pending  , -$order->fleetCommissionValue );
        FleetWalletRedisModel::addBalanceValueByBalanceStatus( BalanceStatus::$Pending  , $fleetCommissionValue);

        if($order->officeId != null && $order->officeCommissionValue > 0  ){
            OfficeWalletRedisModel::addBalanceValueByBalanceStatus($order->officeId, BalanceStatus::$Pending , -$officeCommissionValue);
            OfficeWalletRedisModel::addBalanceValueByBalanceStatus($order->officeId, BalanceStatus::$Pending , $officeCommissionValue);
        }
    }


        $response  = new ReceiveCompletedOrderInfoOutput(['final_price'=>$order->totalAmount] , 'order updated successfully!');
        return $response->send_as_array();
   }
}