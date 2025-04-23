<?php
namespace App\Http\Services\Dashboard\CouponManagement\DestroyCoupon\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class DestroyCouponLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private DestroyCouponInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

   // if(democouponPermission()){
        //     if(request()->is('api/*')){
        //         return comman_message_response( __('messages.demo_permission_denied') );
        //     }
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }
        beginTransaction();
        $coupon = $this->repository->CouponRepository()->deleteRepository()->delete(['id' =>$this->input->getId()]);

        // $service = $this->repository->CouponServiceRepository()->deleteRepository()->delete(['couponId' =>$this->input->getId()]);

        $msg = __('messages.msg_deleted',['name' => __('messages.coupon')] );

        if($coupon == false  ) {
            rollbackTransaction();
            $msg = __('messages.msg_fail_to_delete',['item' => __('messages.coupon')] );
            return comman_custom_response(['message'=> $msg , 'status' => true]);
        }
        commitTransaction();
        return comman_custom_response(['message'=> $msg , 'status' => true]);
   }
}