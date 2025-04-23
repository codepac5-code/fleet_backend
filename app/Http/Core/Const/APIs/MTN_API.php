<?php
namespace App\Http\Core\Const\APIs;



class  MTN_API  {

    static $CashMobileUrl                = "https://cashmobile.mtnsyr.com:9000";
    static $CreatePymentByPhoneNumber    = "pos_web/payment_phone/initiate";
    static $ConfirmPymentPhone           = "pos_web/payment_phone/confirm";
    static $CreateInvoice                = "pos_web/invoice/create";
    static $GetInvoice                   = "pos_web/invoice/get";
    static $Subject                      = "9001000000048959";
    static $TTL = 60;
    // case TerminalID           = " ";
    // case X_Signature          = " ";
    // case Accept_Language      = " ";


    // case header = [
    //     "Subject"=> "9001000000048959",
    //     "Request-Name" => $requestName,
    //     "X-Signature" => $this->makeSignature($body)
    // ];
}
