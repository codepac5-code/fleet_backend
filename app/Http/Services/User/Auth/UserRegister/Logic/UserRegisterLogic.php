<?php
namespace App\Http\Services\User\Auth\UserRegister\Logic;

use App\Http\Core\Classes\ImageManager;
use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;
use Illuminate\Support\Facades\Hash;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\Http;

class UserRegisterLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UserRegisterInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel {

        $userRepository = $this->repository->UserRepository();



        $hashedPassword =  Hash::make($this->input->getPassword());

        $user = $userRepository->readRepository()->getByValue('phoneNumber'   ,$this->input->getPhoneNumber());

        if($user) {
            if ($user->is_registered) {
                make_exception(ErrorMessages::getKey(ErrorMessages::$AccountAlreadyExists,Attributes::User));
            }
            else{
                $userRepository->updateRepository()->update([ 'phoneNumber' =>$this->input->getPhoneNumber() ],
                [
                    'firstName'     =>$this->input->getFirstName(),
                    'lastName'      =>$this->input->getLastName(),
                    'phoneNumber'   =>$this->input->getPhoneNumber(),
                    'dialCode'      =>$this->input->getDialCode(),
                    'password'      =>$hashedPassword,
                ]
            );
          }
        }
        else{

            $ImageManager = new ImageManager();

            $user =  $userRepository->createRepository()->create([
                'firstName'     =>$this->input->getFirstName(),
                'lastName'      =>$this->input->getLastName(),
                'phoneNumber'   =>$this->input->getPhoneNumber(),
                'dialCode'      =>$this->input->getDialCode(),
                'password'      =>$hashedPassword,
                'photo' => $ImageManager->default_profile_photo()
            ]);

            $referralCode = $this->generateReferralCode($user->id);
            $userRepository->updateRepository()->update([ 'id' => $user->id ],
            ['referralCode'  =>$referralCode]
        );
        }

        // $phoneNumber = "963" . substr($this->input->getPhoneNumber(),1);
        $phoneNumber = $this->input->getPhoneNumber();


        // $otpCode = MTNOtpIntegration::sendOtp($phoneNumber);
        $otpCode = $this->send_whatsapp_otp($phoneNumber , $this->input->getFirstName());

        setCatch("user_id_".$user->id , $otpCode);



        $response  = new UserRegisterOutput(  data:$user,
        message: 'الرجاء ادخال كود تأكيد الرقم',
    );

        return $response->send_as_object();
   }


   function generateReferralCode($userId) {
    $prefix = strtoupper(substr(md5($userId . microtime()), 0, 6)); 
    return 'REF-' . $prefix;
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

    public function send_whatsapp_otp($phoneNumber , $userName){
        $otpCode = random_int( 100000 , 999999);

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
                'receiver' => $this->input->getDialCode() . $config['phone'],
                'text' => $this->generateOtpMessage($userName, $otpCode)
            ]);

            if ($response->successful()) {
              return  $otpCode;
            }

            make_exception( 'Failed to send the verification code');

        } catch (\Exception $e) {
           make_exception($e->getMessage());
        }
    }


}
