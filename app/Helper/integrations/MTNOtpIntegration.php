<?php

namespace App\Helper\integrations;

use Illuminate\Support\Facades\Http;
use App\Http\Core\Const\Messages\ErrorMessages;

class MTNOtpIntegration{

    static public function  sendOtp (string $phoneNumber) : int {

        $otpCode = rand(100000, 999999);

       $response = Http::get('https://services.mtnsyr.com:7443/General/MTNSERVICES/ConcatenatedSender.aspx?User=uom424&Pass=mar141214&From=Fleet App&Gsm='.$phoneNumber.'&Msg='.$otpCode.' Is your FleetApp verification code&Lang=1');

        if ($response->body() != $phoneNumber) {
            make_exception(ErrorMessages::getKey(ErrorMessages::$SomeThingWentWrong));
        }

        return $otpCode;

    }

}
