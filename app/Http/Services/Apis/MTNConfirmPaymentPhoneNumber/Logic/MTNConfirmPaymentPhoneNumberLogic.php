<?php
namespace App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Logic;

use Illuminate\Support\Facades\Http;
use App\Http\Core\Const\APIs\MTN_API;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Http\JsonResponse;

class MTNConfirmPaymentPhoneNumberLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private MTNConfirmPaymentPhoneNumberInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse {


            $invoice = $this->repository->MtnInvoiceRepository()->readRepository()
            ->getByValue('id', $this->input->getInvoiceId());

            if ($invoice == null) {
                make_exception("invoice not found" , 400);
            }

            info($this->input->toArray());
            info($invoice);

            $body = [
                "Invoice"           =>  $this->input->getInvoiceId(),
                "Phone"             =>  $invoice->phoneNumber,
                "Guid"              =>  $invoice->guid ,
                "OperationNumber"   =>  $this->input->getOperationNumber(),
                "Code"              =>  base64_encode(hash("sha256",$this->input->getCode(),true))
            ];

            $response = Http::withHeaders( $this->request_header(MTN_API::$ConfirmPymentPhone , $body ))
            ->post(MTN_API::$CashMobileUrl."/".MTN_API::$ConfirmPymentPhone ,$body);

            if($response["Errno"] === 0 ){

                info($response);
                $this->repository->MtnInvoiceRepository()->updateRepository()
                ->update(['id'=>$this->input->getInvoiceId()], [
                    'code'=>        $this->input->getCode(),
                    'operationNumber' =>    $this->input->getOperationNumber(),
                ]);

            }
            else{
                info($response);
                make_exception($response['Error'] ,$response['Errno']);
            }


            // $data['mtn_api_response_data'] = $response['data'];
            $data['invoice'] = $invoice;
            // $data['$invoice'] = $invoice;
            $response  = new MTNConfirmPaymentPhoneNumberOutput( $data , 'success');
            return $response->send_as_object();

            }

            public function request_header( $requestName , $body ) {
                return
                [
                   "Subject"      => MTN_API::$Subject,
                   "Request-Name" => $requestName,
                   "X-Signature"  => makeSignature($body)
                ];
           }

        public function confirmPymentPhone(){


    //    $syraiyel_invoice = $this->repository->SyriatelInvoiceRepository()->readRepository()
    //    ->getByValue('orderId', $this->input->getOrderId());


    //    if ($syraiyel_invoice) {
    //        $body = [
    //            "OTP" =>"$request->otp",
    //            "merchantMSISDN" =>"$syraiyelInvoice->merchantMSISDN",
    //            "transactionID" =>"$syraiyelInvoice->id",
    //            "token" =>"$syraiyelInvoice->token"
    //        ];

    //        $SyraitelResponse = $this->makeSyraitelRequest($this->paymentConfirmationUrl,$body);

    //        if (isset($SyraitelResponse->errorCode) && $SyraitelResponse->errorCode == 0)
    //        {
    //            $syraiyelInvoice->otp = $request->otp;
    //            $syraiyelInvoice->save();
    //            return response()->json(array("status" => 200, "message" => "Payment confirm success", "data" => array("success" => true)));
    //        }
    //        else{
    //            return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
    //        }

    //    }
    //    else
    //    {
    //        return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
    //    }
    }

   }







