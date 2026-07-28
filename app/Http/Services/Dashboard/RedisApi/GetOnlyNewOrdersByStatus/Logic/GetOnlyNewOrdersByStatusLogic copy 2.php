<?php
namespace App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Models\Booking;

class GetOnlyNewOrdersByStatusLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetOnlyNewOrdersByStatusInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $canceled_order_Ids = [] ;
        switch($this->input->getStatus()){
            case OrderStatus::$Pending : 
                $status = OrderStatus::$Pending;
                $canceled_order_Ids  = OrderRedisModel::getCancelOrderIds();
            break;

            case OrderStatus::$OnGoing :  $status = OrderStatus::$OnGoing;
                    $orders = Booking::where('id','>',$this->input->getLastId())
                    ->where('status' , OrderStatus::$OnGoing)->get();
                    $count = $orders->count();
            break;

            case OrderStatus::$Completed :  $status = OrderStatus::$Completed;
                $orders = Booking::where('id','>',$this->input->getLastId())->get();
                $count = $orders->count();
            break;

            default : $status = null;
            break;
        }

        

        // $orders   = OrderRedisModel::getByStatusAfterId(
        //     $status , $this->input->getLastId()
        // );
     
        // $count = OrderRedisModel::get_status_count($status);


        
        return response()->json([
            'orders' => $orders,
            'count'  => $count ?? 0,
            'canceled_order_Ids'=> $canceled_order_Ids ?? [],
        ]);
   }
}