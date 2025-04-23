<?php
namespace App\Http\Services\Apis\SyriatelPaymentApi\Logic;

use App\Http\Core\Const\APIs\Syriatel_API;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Repositories\SyriatelInvoiceRepositories\SyriatelInvoiceRepositoryCaller;
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

    // ----- get token..

    $syriatel_response = $this->makeSyraitelRequest( Syriatel_API::$GetTokenUrl , $body );

    info('bbbbbb  '. $syriatel_response);
    info('mmmmm  ');

    if (isset($syriatel_response->errorCode) && $syriatel_response->errorCode == 0) {
        
        $syriatel_invoice = $this->repository->SyriatelInvoiceRepository()->readRepository()
        ->getByValue("orderId" , $this->input->getOrderId());

    if (!$syriatel_invoice) {

         $this->repository->SyriatelInvoiceRepository()->createRepository()
         ->create([
         "orderId"   => $this->input->getOrderId(),
         "userId"    => $this->input->getUserId(),
         "token"     => $syriatel_response->token
         ]);
    }


        $body = [
            "customerMSISDN"     =>  "".$this->input->getPhoneNumber(),
            "merchantMSISDN"     =>  "". $syriatel_invoice->merchantMSISDN,
            "amount"             =>  $this->input->getAmount(),
            "transactionID"      =>  "".$syriatel_invoice->id,
            "token"              =>  $syriatel_response->token
        ];


        //-------   send payment request..
        $syriatel_response = $this->makeSyraitelRequest(Syriatel_API::$PaymentRequestUrl , $body );

        if (isset($syriatel_response->errorCode) && $syriatel_response->errorCode == 0)
        {

         $syriatel_invoice = $this->repository->SyriatelInvoiceRepository()->updateRepository()
         ->update(['id' => $syriatel_response->id] ,
         [
             'phoneNumber' => $this->input->getPhoneNumber(),
             'amount'      =>$this->input->getAmount()
         ]);
        
         info('222222'. $syriatel_response);

         $response  = new SyriatelPaymentApiOutput($syriatel_response , '');
         return $response->send_as_object();                         // return response()->json(array("status" => 200, "message" => "create request Payment success", "data" => array("success" => true)));
         } 
        else {

        info('3333333'. $syriatel_response);
        make_exception($syriatel_response);
                        // return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
        }
                
 //     return response()->json(array("status" => 200, "message" => "get token success", "data" => array("success" => true)));

         }
        info('444444: '. $syriatel_response);
        make_exception($syriatel_response);
 //    return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
    
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
    try{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Mozilla5.0',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body) . '{"username": "FleetApp", "password": "Fleet@Syria#123"}');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        // if (curl_errno($ch)) {
        //     echo 'Error:' . curl_error($ch);
        // }

        curl_close($ch);

        return json_decode($response);

        } catch (Exception $e) {
            make_exception($e->getMessage());
                 //   return response()->json($e->getMessage());
        }

   }
   
}