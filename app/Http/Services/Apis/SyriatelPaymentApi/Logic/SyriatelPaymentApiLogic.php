<?php
namespace App\Http\Services\Apis\SyriatelPaymentApi\Logic;

use App\Http\Core\Const\APIs\Syriatel_API;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Models\Booking;
use Exception;

class SyriatelPaymentApiLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private SyriatelPaymentApiInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

    $body = [
        "username" => Syriatel_API::$UserName,
        "password" => Syriatel_API::$Password
    ];

    beginTransaction();

    // ----- get token..

    $syriatel_response = $this->makeSyraitelRequest( Syriatel_API::$GetTokenUrl , $body );


    info('syriatel_rrr-token--');
    info(print_r($syriatel_response, true));

    if (!(isset($syriatel_response->errorCode) && $syriatel_response->errorCode == 0)) {
        
        info('syriatel_rrr---');
        info(print_r($syriatel_response, true));
        
        make_exception($syriatel_response->errorDesc);
        rollbackTransaction();
         //    return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
    }

    $invoice_data = [
        "orderId"       => Booking::first()->id,
         "userId"       => $this->input->getUserId(),
         "token"        => $syriatel_response->token,
         "amount"       => $this->input->getAmount(),
         'phoneNumber'  => $this->input->getPhoneNumber(),

    ];

    if($this->input->getOrderId()!= null)  {
        $syriatel_invoice = $this->repository->SyriatelInvoiceRepository()->readRepository()
        ->getByValue("orderId" , $this->input->getOrderId());

   
    if (!$syriatel_invoice) {

        $syriatel_invoice = $this->repository->SyriatelInvoiceRepository()->createRepository()
            ->create($invoice_data);
    }
 } else {
    $syriatel_invoice = $this->repository->SyriatelInvoiceRepository()->createRepository()
    ->create($invoice_data);
}

        $body = [
            "customerMSISDN"     =>  (string) $this->input->getPhoneNumber(),
            "merchantMSISDN"     =>  (string) Syriatel_API::$MerchantMSISDN,
            "amount"             =>  (string) $this->input->getAmount(),
            "transactionID"      =>  (string) $syriatel_invoice->id,
            "token"              =>  (string) $syriatel_response->token
        ];


        //-------   send payment request..
        $syriatel_response = $this->makeSyraitelRequest(Syriatel_API::$PaymentRequestUrl , $body );

        if (isset($syriatel_response->errorCode) && $syriatel_response->errorCode == 0)
        {

        //  $syriatel_invoice = $this->repository->SyriatelInvoiceRepository()->updateRepository()
        //  ->update(['id' => $syriatel_response->id] ,
        //  [
        //      'phoneNumber' => $this->input->getPhoneNumber(),
        //      'amount'      =>$this->input->getAmount()
        //  ]);
        


         commitTransaction();
         $response  = new SyriatelPaymentApiOutput([$syriatel_response,$syriatel_invoice] , 'done successully');
         return $response->send_as_object();                         // return response()->json(array("status" => 200, "message" => "create request Payment success", "data" => array("success" => true)));
         } 
        else {
            
            info('syriatel_rrr---');
info(print_r($syriatel_response, true));

        make_exception($syriatel_response->errorDesc);
        rollbackTransaction();

                        // return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
        }
                
 //     return response()->json(array("status" => 200, "message" => "get token success", "data" => array("success" => true)));

    
 //    } catch (Exception $e) {
 //                return response()->json($e->getMessage());
     }


     
// public function payment_request($invoice) {

//         $body =[
//             "customerMSISDN"     =>  "".$this->input->getPhoneNumber(),
//             "merchantMSISDN"     =>  "". $invoice->merchantMSISDN,
//             "amount"             =>  $this->input->getAmount(),
//             "transactionID"      =>  "".$invoice->id,
//             "token"              =>  $invoice->token
//         ];

//         $syriatel_response = $this->makeSyraitelRequest(Syriatel_API::$PaymentRequestUrl , $body );

//         if (isset($syriatel_response->errorCode) && $syriatel_response->errorCode == 0)
//         {

//          $syriatel_invoice = $this->repository->SyriatelInvoiceRepository()->updateRepository()
//          ->update(['id' => $invoice->id] ,
//          [
//              'phoneNumber' => $this->input->getPhoneNumber(),
//              'amount'      =>$this->input->getAmount()
//          ]);
//          return true;
//                          // return response()->json(array("status" => 200, "message" => "create request Payment success", "data" => array("success" => true)));
//         } else{

//          return false;                  // return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
//         }


//    }



//    public function resendtOTP(Request $request){

// try{
//        $validator = Validator::make($request->all(), [
//            "order_id" => ['integer',"required"]
//        ]);
//        if ($validator->fails()) {
//            return response()->json(array("status" => 400, "message" => $validator->errors()->first(), "data" => array()));
//        }

//        $syraiyelInvoice = SyriatelInvoice::query()->where("order_id","=",$request->order_id)->first();

//        if ($syraiyelInvoice) {
//            $body = [
//                "merchantMSISDN" =>$syraiyelInvoice->merchantMSISDN,
//                "transactionID" =>"$syraiyelInvoice->id",
//                "token" =>$syraiyelInvoice->token
//            ];

//            $SyraitelResponse = $this->makeSyraitelRequest($this->resendOTPUrl,$body);

//            if (isset($SyraitelResponse->errorCode) && $SyraitelResponse->errorCode == 0)
//            {
//                return response()->json(array("status" => 200, "message" => "resend otp success", "data" => array("success" => true)));
//            }
//            else{
//                return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
//            }

//        }
//        else
//        {
//            return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
//        }
//        } catch (Exception $e) {
//                    return response()->json($e->getMessage());
//        }
//    }



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