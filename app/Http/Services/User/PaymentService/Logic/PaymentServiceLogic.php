<?php
namespace App\Http\Services\User\PaymentService\Logic;
use App\Http\Core\Response\SendResponse;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Apis\MTNPaymentApi\Logic\MTNPaymentApiInput;
use App\Http\Services\Apis\MTNPaymentApi\Logic\MTNPaymentApiLogic;
use App\Http\Services\Apis\StripePayment\Logic\StripePaymentInput;
use App\Http\Services\Apis\StripePayment\Logic\StripePaymentLogic;
use App\Http\Services\Apis\SyriatelPaymentApi\Logic\SyriatelPaymentApiInput;
use App\Http\Services\Apis\SyriatelPaymentApi\Logic\SyriatelPaymentApiLogic;
use App\Services\StripeService;

class PaymentServiceLogic  {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function __call($name , $arguments) {
        
        return SendResponse::sendFiledResponse(new ResponseModel(
            null,
            "The '".$name. "' payment is not available!"
        )); 
    }

    public function stripe($request){

        // validate input data and pass it to the service..
        $input = new StripePaymentInput($request->validated());

        $service = new StripePaymentLogic($input); // call the service's logic
        // execute service and get result..
        $result = $service->execute();


    }
    
   public function syriatel($request) {
         // validate input data and pass it to the service..
         $input = new SyriatelPaymentApiInput($request);

         $service = new SyriatelPaymentApiLogic($input); // call the service's logic

         // execute service and get result..
        return  $result = $service->execute();
   }


   public function mtn($request){

            // validate input data and pass it to the service..
            $input = new MTNPaymentApiInput($request);

            $service = new MTNPaymentApiLogic($input); // call the service's logic
    
            // execute service and get result..
            return $result = $service->execute();
   }

    public function cash($request){
        
    }


    
   
}