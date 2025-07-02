<?php
namespace App\Http\Services\Dashboard\BookingManagement\ChangeOrderStatus\Logic;

use App\Events\HoldOrder;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\Order\OrderRedisModel;
use App\Http\Core\SubSystems\RedisDatabase\RedisModels\RedisModel;

class ChangeOrderStatusLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ChangeOrderStatusInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {


        $order = $this->repository->BookingRepository()->readRepository()
        ->getByValue('id',$this->input->getOrderId());

        
        if ($order) {
            switch ($this->input->getStatus()){
                case 'hold' :
                    $order_updated = $this->repository->BookingRepository()->updateRepository()
                    ->update(['id'=>$this->input->getOrderId()] ,['status'=>OrderStatus::$Hold ,'reason'=>$this->input->getReason()]);
                    if($order_updated > 0 ){
                        OrderRedisModel::delete( $order->id , OrderStatus::$OnGoing );
                        event(new HoldOrder($order->id));
                        return response()->json(['success' => true]);
                    }
                    break;

                case 'cancel' :
                    $order_updated = $this->repository->BookingRepository()->updateRepository()
                    ->update(['id'=>$this->input->getOrderId()] ,['status'=>OrderStatus::$Cancelled,'reason'=>$this->input->getReason()]);
                    if($order_updated > 0 ){
                        OrderRedisModel::delete( $order->id , OrderStatus::$OnGoing );
                        event(new HoldOrder($order->id));
                        return response()->json(['success' => true]);
                    }
                    break;

            }

            return response()->json([ 
                'success' => false, 
                'message' => 'لم يتـم تحديث الحالة الرجاء اعادة المحاولة'
              ]);
              
      } else {
        return response()->json([ 
              'success' => false, 
              'message' => 'الطلب غير موجود'
            ]);
        }
   }
}