<?php
namespace App\Http\Services\Api\Send_SMS_Message_Api\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\Cache;

class Send_SMS_Message_ApiLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories


    public function __construct(
    //---------------------------------------------------------------------------------------
    private Send_SMS_Message_ApiInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $smsService = new SmsalaService();

        $otp = rand(100000, 999999);
        Cache::put("otp_{$this->input->getPhoneNumber()}", $otp, now()->addMinutes(5));

        $sent = $smsService->sendOtp($this->input->getPhoneNumber(), $otp);

        if ($sent) {
            $response  = new Send_SMS_Message_ApiOutput([] , 'OTP sent successfully');
            return $response->send_as_array();
            return response()->json(['message' => 'OTP sent successfully']);
        }
        make_exception( 'Failed to send OTP');
   }


}