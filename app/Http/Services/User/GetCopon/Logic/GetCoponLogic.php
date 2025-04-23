<?php
namespace App\Http\Services\User\GetCopon\Logic;

use Illuminate\Support\Facades\DB;
use GrahamCampbell\ResultType\Error;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class GetCoponLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetCoponInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

            $coupon = $this->repository->CouponRepository()->readRepository()->getFirstByConditions([
                'code' => $this->input->getCoponCode()
            ]);

            if ($coupon == null) {
                make_exception(__('messages.coupon_not_found')); 
            }

            $coponUser = $this->repository->CoponUserRepository()->readRepository()->getFirstByConditions([
                'couponId' => $coupon->id,
                'userId' => $this->input->getUserId()
            ]);

            if ($coponUser == null) {
                make_exception(__('messages.coupon_not_found'));
             }

            if ($coponUser->count >= $coupon->limit) {
                make_exception('sorry , your copon is used before'); 
            }

            // $service = $this->repository->CouponServiceRepository()->readRepository()->getFirstByConditions([
            //     'serviceId' => $this->input->getServiceId(),
            //     'couponId' => $coupon->id
            // ]);

            
            
    
            // if (!$service ) {
            //     make_exception('Sorry , this coupon is for'. $service->title. 'service');
            // }

            // $masg = 'a discount of ' . $coupon->discount . 'has been deducted from your service total.';
            $masg = __('message.coupon_discount', ['discount' => $coupon->discount]);

            $data = [];
            $discount = 0;
            if ($coupon->isPercentage) {
                $discount = $coupon->discount /100;
                $data['newPrice'] = (int)($this->input->getPrice() - ($this->input->getPrice() * ($discount) ));
                $masg = __('message.coupon_discount', ['discount' => $coupon->discount]);

                // $masg = 'a discount of ' . $coupon->discount . '% has been deducted from your service total.';
            }
            else
            {
                $discount = $coupon->discount;
                $data['newPrice'] = (int)($this->input->getPrice() - $discount);
                if($data['newPrice'] < 0){
                make_exception('the coupon code entered is invalid');
                 }
            }



        $response  = new GetCoponOutput([
            'discount'=> $discount,
            'isPercentage' => $coupon->isPercentage
        ] ,
         $masg//SuccessMessages::$couponData
        );

        return $response->send_as_object();
   }


   function getTwoDecimals(float $number): string {
    $decimalPart = explode('.', (string)$number);
    
    if (isset($decimalPart[1])) {
        $decimals = $decimalPart[1];
        
        if (strlen($decimals) < 2) {
            $decimals = str_pad($decimals, 2, '0'); 
        } else {
            $decimals = substr($decimals, 0, 2); 
        }
    } else {
        $decimals = '00'; 
    }

    return $decimals;
}
}
