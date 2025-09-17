<?php
namespace App\Http\Services\User\Auth\UserSendOtpServiceService\Logic;

use App\Helper\integrations\MTNOtpIntegration;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Repositories\UserRepositories\UserReadRepository;
use Illuminate\Support\Facades\Http;

class UserSendOtpServiceServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UserSendOtpServiceServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {


    $userReadRepository = $this->repository->UserRepository()->readRepository();
    $user = $userReadRepository->getByValue( 'phoneNumber' , $this->input->getPhoneNumber());

    if($user == null){
        make_exception(__('messages.user_already_exists'));
    }




    // $otpCode = MTNOtpIntegration::sendOtp($phoneNumber);
    $otpCode = $this->send_whatsapp_otp($this->input->getPhoneNumber(),$this->input->getDialCode() , $user->firstName );

    setCatch("user_id_".$user->id , $otpCode);

        $response  = new UserSendOtpServiceServiceOutput([
            "phoneNumber" => $this->input->getPhoneNumber(),
            "userId" => $user->id
        ] , '');

        return $response->send_as_array();
   }


   function generateOtpMessage($userName , $otpCode) {
    if(app()->getLocale() == 'ar'){
        return "مرحباً {$userName} 👋\n" .
           "رمز التحقق الخاص بك هو: {$otpCode}\n" .
           "⏱️ صالح لمدة 1 دقيقة فقط.\n" .
           "⚠️ لا تشارك هذا الرمز مع أي شخص لضمان أمان حسابك على Fleet.";
    }

    return "Hello {$userName} 👋\n" .
           "Your verification code is: {$otpCode}\n" .
           "⏱️ This code is valid for 1 minute only.\n" .
           "⚠️ Do not share this code with anyone to keep your Fleet account secure.";
}

    public function send_whatsapp_otp($phoneNumber ,$dialCode, $userName){
        $otpCode = random_int(100000, 999999);

        $config = [
            'base_url' => 'https://message.dashboard.technoplus.tech',
            'api_key' => '51|OE7uqfEx7BJzGpzCtFBHC9McoZxJT25oG9jybb5e72747175',
            'session_id' => '1102d8e0-f7d2-457c-b1db-a1d4287fee37',
            'phone' => $phoneNumber, 
            'otp' => $otpCode
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Accept' => 'application/json',
            ])->post($config['base_url'] . '/whatsapp/api/v1/message/text/send', [
                'session_id' => $config['session_id'],
                'receiver' => $this->input->getDialCode() . $phoneNumber,
                // 'receiver' => '+963' . substr($config['phone'], 1),
                'text' => $this->generateOtpMessage($userName, $otpCode)
            ]);

            if ($response->successful()) {
              return  $otpCode;
            }

            make_exception( 'Failed to send the verification code');

        } catch (\Exception $e) {
           make_exception('otp error');
        }
    }
}
