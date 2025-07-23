<?php
namespace App\Http\Services\User\InquireUserOrderStatus\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class InquireUserOrderStatusLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private InquireUserOrderStatusInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        // get auth user
        $user = getAuthUser();

        // init..
        $response_data['hold'] = false;
        $response_data['order'] = null;

        // -------------------
        $order = $this->repository->BookingRepository()
        ->readRepository()
        ->getByValue('id', $this->input->getOrderId());


        // if(($order != null)){
        //     $response_data['order'] = $order;
        // }

        if( $order->status == OrderStatus::$Hold || $order->status == OrderStatus::$Cancelled){
            $response_data = ['hold'=>true , 'order'=>$order];
        }



        $response  = new InquireUserOrderStatusOutput($response_data , 'this is your order status');
        return $response->send_as_object();
   }
}