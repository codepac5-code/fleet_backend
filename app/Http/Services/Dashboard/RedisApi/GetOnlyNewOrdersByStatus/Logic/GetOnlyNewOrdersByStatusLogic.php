<?php
namespace App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;

class GetOnlyNewOrdersByStatusLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetOnlyNewOrdersByStatusInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init
        // repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $canceled_order_Ids = [] ;
        switch($this->input->getStatus()){
            case OrderStatus::$Pending :
                $status = OrderStatus::$Pending;
                $canceled_order_Ids  = Booking::where('status', OrderStatus::$Cancelled)
                                    ->latest()
                                    ->limit(30)
                                    ->pluck('id');
                // OrderRedisModel::getCancelOrderIds();
            break;


            case OrderStatus::$OnGoing :  $status = OrderStatus::$OnGoing;
            break;

            case OrderStatus::$Completed :  $status = OrderStatus::$Completed;
            break;


            default : $status = '';
            break;
        }



        // $orders   = OrderRedisModel::getByStatusAfterId(
        //     $status , $this->input->getLastId()
        // );

        $orders = $this->repository->BookingRepository()
        ->readRepository()
        ->getOrdersByStatusAfterId( $status ,  $this->input->getLastId());


        $count = count($orders);



        return response()->json([
            'orders' => $orders,
            'count'  => $count ?? 0,
            'canceled_order_Ids'=>$canceled_order_Ids ?? [],
        ]);
   }
}
