<?php
namespace App\Http\Core\Const\APIs;



class  Syriatel_API  {

    static public $UserName     = "FleetApp";
    static public $Password     = "Fleet@Syria#123";
    static public $GetTokenUrl  = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken";
    static public $PaymentRequestUrl = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentRequest";
    static public $PaymentConfirmationUrl = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentConfirmation";
    static public $ResendOTPUrl = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/resendOTP";
    static public $MerchantMSISDN = "0930302829";

}
