<?php
namespace App\Http\Services\Apis\SyriatelPaymentApi\Logic;

use App\Http\Core\Const\APIs\Syriatel_API;
use App\Http\Core\Const\Options\OrderStatus;
use App\Http\Core\Const\Options\PaymentType;
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

        

        $this->repository->BookingRepository()->updateRepository()->update(
                ['id'=>$this->input->getOrderId()],[
                    'status'    => OrderStatus::$Completed ,
                    'paymentType' => PaymentType::$Electronic,
                    'paymentStatus'=> 'paid',
                    'PaymentDatetime'=>now()
                ]
            );
         commitTransaction();

         $response  = new SyriatelPaymentApiOutput( [
            'operationNumber'=> 0,
            'invoiceId'=> $syriatel_invoice->id]  , 'invoice created successfully!');
         return $response->send_as_object();    
        }                   
        else {
            
        info('syriatel_rrr---');
        info(print_r($syriatel_response, true));

        make_exception($syriatel_response->errorDesc);
        rollbackTransaction();
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