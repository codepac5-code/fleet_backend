<?php
namespace App\Http\Services\Driver\ReceiveCompletedOrderInfo\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

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
        ->getByValue('id',$this->input->getOrderId());

        if($order == null){
            make_exception('order not found!');
        }


        if($order->couponId != null){
            $coupon = $order->coupon;
            $new_price = $coupon->discount * $this->input->getPrice();
        }


        $order = $this->repository->BookingRepository()
        ->updateRepository()
        ->update(['id'=>$this->input->getOrderId()],[
            'time'          =>$this->input->getTime(),
            'distance'        =>$this->input->getDistance(),
            'amount'        =>$this->input->getPrice(),
            'totalAmount'   => $this->input->getPrice()
        ]
    );

        $response  = new ReceiveCompletedOrderInfoOutput([] , 'order updated successfully!');
        return $response->send_as_array();
   }
}