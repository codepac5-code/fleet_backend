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
        

        $orders   = OrderRedisModel::getByStatusAfterId(
            $status , $this->input->getLastId()
        );
     
        $count = OrderRedisModel::get_status_count($status);

        

        
        return response()->json([
            'orders' => $orders,
            'count'  => $count ?? 0,
        ]);
   }
}