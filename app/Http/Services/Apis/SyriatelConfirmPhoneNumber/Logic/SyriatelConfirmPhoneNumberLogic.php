<?php
namespace App\Http\Services\Apis\SyriatelConfirmPhoneNumber\Logic;

use App\Http\Core\Const\APIs\Syriatel_API;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Exception;

class SyriatelConfirmPhoneNumberLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private SyriatelConfirmPhoneNumberInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {


        $syraiyelInvoice = $this->repository->SyriatelInvoiceRepository()
        ->readRepository()->getByValue(
            'id' ,$this->input->getInvoiceId()
        );
        
        if($syraiyelInvoice == null){
            return make_exception("invoice not found" , 400);
            // return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
        }

        $body = [
            "OTP"            => (string) $this->input->getCode(),
            "merchantMSISDN" => (string) Syriatel_API::$MerchantMSISDN,
            "transactionID"  => (string) $this->input->getInvoiceId(),
            "token"          => (string) $syraiyelInvoice->token
        ];

        $SyraitelResponse = $this->makeSyraitelRequest(Syriatel_API::$PaymentConfirmationUrl , $body);

        if (isset($SyraitelResponse->errorCode) && $SyraitelResponse->errorCode == 0)
        {
            $syraiyelInvoice->code = $this->input->getCode();
            $syraiyelInvoice->save();
            $response  = new SyriatelConfirmPhoneNumberOutput(["success" => true] , "Payment confirm success");
            return $response->send_as_object();
            
            // return response()->json(array("status" => 200, "message" => "Payment confirm success", "data" => array("success" => true)));
        }
        else{
            
            make_exception($SyraitelResponse->errorDesc);
            // return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
        }

   }




   public function makeSyraitelRequest($url , $body) {
    try {
        $ch = curl_init();
    
        $mergedBody = array_merge($body, [
            "username" => Syriatel_API::$UserName,
            "password" => Syriatel_API::$Password
        ]);
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Mozilla5.0',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mergedBody));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        info("HTTP Status: $httpCode");
        info("Raw Response: " . $response);
    
        return json_decode($response);
    
    } catch (Exception $e) {
        return response()->json($e->getMessage());
    }
    
       }
}