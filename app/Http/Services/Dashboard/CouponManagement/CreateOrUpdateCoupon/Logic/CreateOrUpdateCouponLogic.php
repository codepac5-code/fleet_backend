<?php
namespace App\Http\Services\Dashboard\CouponManagement\CreateOrUpdateCoupon\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class CreateOrUpdateCouponLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private CreateOrUpdateCouponInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {
        // if(demoUserPermission()){
        //     return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        // }

        $serviceIds = $this->input->getServiceIds(); 
        $data = array();

        beginTransaction();
        if($this->input->getId()!= null ){
            
            $coupon = $this->repository->SliderRepository()->createRepository()
        ->updateOrCreate(
            ['id'=>  $this->input->getId()], 
            [
              'code' => $this->input->getCode(),
              'discounType' => $this->input->getDiscounType(),
              'discount' => $this->input->getDiscount(),
              'expireDate' => $this->input->getExpireDate(),
              'isActive' => $this->input->getIsActive(),
              'limit'=> $this->input->getLimit(),
          ]);

          $this->repository->CouponServiceRepository()->deleteRepository()->delete(['couponId'=>  $this->input->getId()]);

        } else{

            $coupon = $this->repository->CouponRepository()->createRepository()
        ->create([
            'code' => $this->input->getCode(),
            'discounType' => $this->input->getDiscounType(),
            'discount' => $this->input->getDiscount(),
            'expireDate' => $this->input->getExpireDate(),
            'isActive' => $this->input->getIsActive(),
            'limit'=> $this->input->getLimit(),
          ]);
        }


        if( $coupon == null ){
            rollbackTransaction();
            return  redirect()->back()->withErrors(trans("حدث خطأ ما يرجى اعادة المحاولة"));
           }

        // if(!empty($serviceIds)){
            foreach ($serviceIds as $serviceId) {
              array_push($data, [ 'serviceId'=> $serviceId , 'couponId'=> $coupon->id]);
            }
            $this->repository->CouponServiceRepository()->createRepository()->insertOrIgnore($data);
        //}

        
        // $coupon->assignRole('coupon');

        $message = __('messages.update_form',[ 'form' => __('messages.coupon') ] );
		if( $coupon->wasRecentlyCreated ){
			$message = __( 'messages.save_form',[ 'form' => __('messages.coupon') ] );
		}
        commitTransaction();
		return redirect(route('coupon.index'))->withSuccess($message);
   }
}