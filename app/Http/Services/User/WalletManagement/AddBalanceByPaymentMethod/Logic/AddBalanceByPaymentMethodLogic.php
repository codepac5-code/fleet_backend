<?php
namespace App\Http\Services\User\WalletManagement\AddBalanceByPaymentMethod\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Apis\MTNPaymentApi\Logic\MTNPaymentApiInput;
use App\Http\Services\Apis\MTNPaymentApi\Logic\MTNPaymentApiLogic;
use App\Http\Services\Apis\SyriatelPaymentApi\Logic\SyriatelPaymentApiInput;
use App\Http\Services\Apis\SyriatelPaymentApi\Logic\SyriatelPaymentApiLogic;

class AddBalanceByPaymentMethodLogic {

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
    
   public function syriatel($request) {
         // validate input data and pass it to the service..
         $input = new SyriatelPaymentApiInput($request);

         $service = new SyriatelPaymentApiLogic($input); // call the service's logic

         // execute service and get result..
        return  $result = $service->execute();
   }


   public function mtn($request){

            // validate input data and pass it to the service..
            $input = new MTNPaymentApiInput($request->all());

            $service = new MTNPaymentApiLogic($input); // call the service's logic
    
            // execute service and get result..
            return $result = $service->execute();
   }

    public function cash($request){
        
    }
}