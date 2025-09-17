<?php
namespace App\Http\Services\Apis\MTNPaymentApi\Logic;

use App\Helper\Helper;
use App\Http\Core\Const\APIs\MTN_API;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Const\Options\PaymentType;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\Http;

class MTNPaymentApiLogic implements Service {


    private RepositoryCaller $repository ; // access to all model's repositories


    public function request_header($requestName , $body) {
        return [
            "Subject"=> MTN_API::$Subject,
            "Request-Name" => $requestName,
            "X-Signature" => makeSignature($body)
        ];
}


    public function __construct(
    //---------------------------------------------------------------------------------------
    private MTNPaymentApiInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        // $invoice = $this->repository->MtnInvoiceRepository()->readRepository()
        // ->getByValue('orderId',$this->input->getOrderId());


        // if ($invoice == null) {
        $invoice = $this->repository->MtnInvoiceRepository()->createRepository()->create([
            "amount"    => $this->input->getAmount() ,
            "TTL"       => MTN_API::$TTL ,
            "phoneNumber" => $this->input->getPhoneNumber(),
            // "operationNumber"=>'',
            "guid"      => $this->guid(),
            "userId"    => $this->input->getUserId(),
            "orderId"   => $this->input->getOrderId()
        ]);

        $body = [
            "Amount"     => $this->input->getAmount() * 100 ,
            "Invoice"    => $invoice->id,
            "TTL"        => MTN_API::$TTL
        ];

        $mtn_response = Http::withHeaders( $this->request_header( MTN_API::$CreateInvoice , $body ))
        ->post(MTN_API::$CashMobileUrl.'/'.MTN_API::$CreateInvoice,$body);

        if($mtn_response["Errno"] != 0 ){
            $this->repository->MtnInvoiceRepository()->deleteRepository()->delete(['id'=>$invoice->id]);
            make_exception( $mtn_response["Error"] , $mtn_response["Errno"] );
        }

        // }

        $mtn_response = Http::withHeaders($this->request_header(MTN_API::$GetInvoice ,["Invoice" => $invoice->id] ))
        ->post( MTN_API::$CashMobileUrl.'/'.MTN_API::$GetInvoice ,  ["Invoice" => $invoice->id]);

        if ($mtn_response['Errno'] != 0) {
                make_exception( $mtn_response["Error"] , $mtn_response["Errno"]);
        }
        info($mtn_response);
        $payment_response = $this->initPymentPhone($invoice, $this->input->getPhoneNumber());

        info($payment_response);

        $this->repository->BookingRepository()->updateRepository()->update(
            ['id'=>$this->input->getOrderId()],[
                'status'    => OrderStatus::$Completed ,
                'paymentType' => PaymentType::$Electronic,
                'paymentStatus'=> 'paid',
                'PaymentDatetime'=>now()
            ]
        );
        
        $response  = new MTNPaymentApiOutput( [
            'operationNumber'=> $payment_response['OperationNumber'],
            'invoiceId'=> $invoice->id] ,
            
            //------ mtn message
            $payment_response['PaySystem'] ?? '');
        return $response->send_as_object();

        }


    public function initPymentPhone($invoice  , $phoneNumber  ) {

        $body = ["Invoice"=>$invoice->id ,"Phone"=>$phoneNumber,"Guid" =>$invoice->guid ];

        $response = Http::withHeaders($this->request_header( MTN_API::$CreatePymentByPhoneNumber , $body ))
        ->post( MTN_API::$CashMobileUrl.'/'.MTN_API::$CreatePymentByPhoneNumber , $body );


        if($response["Errno"] != 0 ){
            make_exception( $response["Error"] , $response["Errno"] );
        }
        return $response;

    }




    function guid(){
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');
        
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }


}
