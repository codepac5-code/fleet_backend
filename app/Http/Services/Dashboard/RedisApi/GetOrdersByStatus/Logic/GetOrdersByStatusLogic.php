<?php
namespace App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Logic;

use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Models\Booking;
use Carbon\Carbon;

class GetOrdersByStatusLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetOrdersByStatusInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $canceled_order_Ids = [] ;
        $page   = $this->input->getPage();  
        $limit  = 7;                      
        $offset = ($page - 1) * $limit;  

        switch($this->input->getStatus()){
            case OrderStatus::$Pending :
                 $status = OrderStatus::$Pending;
                 $canceled_order_Ids  = OrderRedisModel::getCancelOrderIds();

                 $orders = OrderRedisModel::getByStatusPaginated($status , $offset , $limit);
                 $count  = OrderRedisModel::get_status_count($status);
            break;

            case OrderStatus::$OnGoing :
                $status = OrderStatus::$OnGoing;
                $query = Booking::scopeForCurrentUser()
                  ->where('status', $status);
              
                $count  = $query->count();
                $orders = $query->skip($offset)->take($limit)->get();
            break;

            case OrderStatus::$Completed : 
                $query = Booking::scopeForCurrentUser()
                ->where('status', OrderStatus::$Completed)
                ->whereDate('created_at', Carbon::today());
            
                $count  = $query->count();
                $orders = $query->skip($offset)->take($limit)->get();
            break;

            default : $status = ' ';
            break;
        }


        // $ongoing_count   = OrderRedisModel::get_ongoing_count();
        // $pending_count   = OrderRedisModel::get_pending_count();
                
        $total_pages = ceil($count / $limit);

        
        return response()->json([
            'orders' => $orders,
            'current_page' => ceil(($offset + 1) / $limit),
            'total_pages' => $total_pages,
            'count'  => $count ?? 0,
            'canceled_order_Ids'=>$canceled_order_Ids ?? [],

            // 'ongoing_orders'   => $ongoing_orders,
            // 'pending_orders'   => $pending_orders,
            // 'ongoing_count'    => $ongoing_count ?? 0,
            // 'pending_count'    => $pending_count ?? 0,
            // 'total_completed_pages' => $total_completed_pages,
            // 'total_ongoing_pages'   => $total_ongoing_pages,
            // 'total_pending_pages'   => $total_pending_pages,
        ]);
   }
}