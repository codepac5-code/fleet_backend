<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SyriatelInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;


class SyriatelApis extends Controller
{
    public $getTokenUrl = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/getToken";
    public $paymentRequestUrl = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentRequest";
    public $paymentConfirmationUrl = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/paymentConfirmation";
    public $resendOTPUrl = "https://merchants.syriatel.sy:1443/ePayment_external_Json/rs/ePaymentExternalModule/resendOTP";


    public function getToken(Request $request) {


        try {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            "order_id" => ['integer',"required"],
        ]);
        if ($validator->fails()) {
            return response()->json(array("status" => 400, "message" => $validator->errors()->first(), "data" => array("success" => false)));
        }

        $body = [
            "username" => "FleetApp",
            "password" => "Fleet@Syria#123"
        ];

        $SyraitelResponse = $this->makeSyraitelRequest($this->getTokenUrl,$body);


        if (isset($SyraitelResponse->errorCode) && $SyraitelResponse->errorCode == 0) {
            $syraiyelInvoice = SyriatelInvoice::query()->where("order_id","=",$request->order_id)->first();
            if (!$syraiyelInvoice) {
                $syraiyelInvoice = SyriatelInvoice::query()->create([
                    "order_id" => $request->order_id,
                    "user_id" => $user->id,
                    "token" => $SyraitelResponse->token
                ]);
            }
            else
            {
                $syraiyelInvoice->token = $SyraitelResponse->token;
                $syraiyelInvoice->save();
            }
            return response()->json(array("status" => 200, "message" => "get token success", "data" => array("success" => true)));

        }
        return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
        
        } catch (Exception $e) {
                    return response()->json($e->getMessage());
        }

    }


    public function pymentRequest(Request $request) {

    try{

        $validator = Validator::make($request->all(), [
            "userPhone" => ['string',"required"],
            "amount" => ['string',"required"],
            "order_id" => ['integer',"required"],
        ]);
        if ($validator->fails()) {
            return response()->json(array("status" => 400, "message" => $validator->errors()->first(), "data" => array("success" => false)));
        }

        $syraiyelInvoice = SyriatelInvoice::query()->where("order_id","=",$request->order_id)->first();

        if ($syraiyelInvoice) {
            $body =[
                "customerMSISDN"=>  "$request->userPhone",
                "merchantMSISDN"=>  "$syraiyelInvoice->merchantMSISDN",
                "amount"=>           $request->amount,
                "transactionID"=>   "$syraiyelInvoice->id",
                "token"=>            $syraiyelInvoice->token
            ];

            $SyraitelResponse = $this->makeSyraitelRequest($this->paymentRequestUrl,$body);

            if (isset($SyraitelResponse->errorCode) && $SyraitelResponse->errorCode == 0)
            {
                $syraiyelInvoice->userPhone = $request->userPhone;
                $syraiyelInvoice->amount = $request->amount;
                $syraiyelInvoice->save();
                return response()->json(array("status" => 200, "message" => "create request Payment success", "data" => array("success" => true)));
            }
            else{
                return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
            }

        }
        else
        {
            return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
        }
        
    } catch (Exception $e) {
                    return response()->json($e->getMessage());
        }


    }

    public function confirmPymentPhone(Request $request){

try{
        $validator = Validator::make($request->all(), [
            "otp" => ['string',"required"],
            "order_id" => ['integer',"required"]
        ]);
        if ($validator->fails()) {
            return response()->json(array("status" => 400, "message" => $validator->errors()->first(), "data" => array("success" => false)));
        }

        $syraiyelInvoice = SyriatelInvoice::query()->where("order_id","=",$request->order_id)->first();

        if ($syraiyelInvoice) {
            $body = [
                "OTP" =>"$request->otp",
                "merchantMSISDN" =>"$syraiyelInvoice->merchantMSISDN",
                "transactionID" =>"$syraiyelInvoice->id",
                "token" =>"$syraiyelInvoice->token"
            ];

            $SyraitelResponse = $this->makeSyraitelRequest($this->paymentConfirmationUrl,$body);

            if (isset($SyraitelResponse->errorCode) && $SyraitelResponse->errorCode == 0)
            {
                $syraiyelInvoice->otp = $request->otp;
                $syraiyelInvoice->save();
                return response()->json(array("status" => 200, "message" => "Payment confirm success", "data" => array("success" => true)));
            }
            else{
                return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
            }

        }
        else
        {
            return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
        }
} catch (Exception $e) {
                    return response()->json($e->getMessage());
        }

    }

    public function resendtOTP(Request $request){

try{
        $validator = Validator::make($request->all(), [
            "order_id" => ['integer',"required"]
        ]);
        if ($validator->fails()) {
            return response()->json(array("status" => 400, "message" => $validator->errors()->first(), "data" => array()));
        }

        $syraiyelInvoice = SyriatelInvoice::query()->where("order_id","=",$request->order_id)->first();

        if ($syraiyelInvoice) {
            $body = [
                "merchantMSISDN" =>$syraiyelInvoice->merchantMSISDN,
                "transactionID" =>"$syraiyelInvoice->id",
                "token" =>$syraiyelInvoice->token
            ];

            $SyraitelResponse = $this->makeSyraitelRequest($this->resendOTPUrl,$body);

            if (isset($SyraitelResponse->errorCode) && $SyraitelResponse->errorCode == 0)
            {
                return response()->json(array("status" => 200, "message" => "resend otp success", "data" => array("success" => true)));
            }
            else{
                return response()->json(array("status" => 400, "message" => $SyraitelResponse, "data" => array("success" => false)));
            }

        }
        else
        {
            return response()->json(array("status" => 400, "message" => "error try agine", "data" => array("success" => false)));
        }
        } catch (Exception $e) {
                    return response()->json($e->getMessage());
        }
    }



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
                    return response()->json($e->getMessage());
        }
    }
}
