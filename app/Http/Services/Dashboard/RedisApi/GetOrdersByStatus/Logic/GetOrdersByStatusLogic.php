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

        switch($this->input->getStatus()){
            case 'pending' : $status = OrderStatus::$Pending;
            break;

            case 'ongoing' :  $status = OrderStatus::$OnGoing;
            break;

            case 'completed' :  $status = OrderStatus::$Completed;
            break;

            default : $status = ' ';
            break;
        }
        
        $page = $this->input->getPage();
        $limit = 7;
        $offset = ($page - 1) * $limit;

        $orders   = OrderRedisModel::getByStatusPaginated($status , $offset , $limit);
     
     
        $count = OrderRedisModel::get_status_count($status);
        // $ongoing_count   = OrderRedisModel::get_ongoing_count();
        // $pending_count   = OrderRedisModel::get_pending_count();
        
        
        $total_pages = ceil($count / $limit);


        
        return response()->json([
            'orders' => $orders,
            'current_page' => ceil(($offset + 1) / $limit),
            'total_pages' => $total_pages,
            'count'  => $count ?? 0,
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