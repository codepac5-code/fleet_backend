<?php
namespace App\Http\Services\Api\Send_SMS_Message_Api\Logic;


use Illuminate\Support\Facades\Http;

class SmsalaService
{
    protected $username;
    protected $password;
    protected $apiId;

    public function __construct()
    {
        $this->username = env('SMSALA_USERNAME');
        $this->password = env('SMSALA_PASSWORD');
        $this->apiId = env('SMSALA_API_ID');
    }

    public function sendOtp($phone, $otp)
    {
        $message = "رمز التحقق الخاص بـ Fleet هو: $otp";

        $response = Http::get('https://smsala.com/api/v1/send', [
            'username' => $this->username,
            'password' => $this->password,
            'api_id'   => $this->apiId,
            'to'       => $phone,
            'msg'      => $message,
            'type'     => 'text',
            'lang'     => 'ar',
        ]);

        return $response->successful();
    }
}
