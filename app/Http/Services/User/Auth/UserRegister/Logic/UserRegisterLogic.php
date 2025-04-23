<?php
namespace App\Http\Services\User\Auth\UserRegister\Logic;

use App\Helper\integrations\MTNOtpIntegration;
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

        if ($user) {
            if ($user->is_registered) {
                make_exception(ErrorMessages::getKey(ErrorMessages::$AccountAlreadyExists,Attributes::User));
            }
            else{
                $userRepository->updateRepository()->update([ 'phoneNumber' =>$this->input->getPhoneNumber() ],
                [
                    'firstName'     =>$this->input->getFirstName(),
                    'lastName'      =>$this->input->getLastName(),
                    'phoneNumber'   =>$this->input->getPhoneNumber(),
                    'password'      =>$hashedPassword,
                ]
            );
            }
        }
        else{
            $user =  $userRepository->createRepository()->create([
                'firstName'     =>$this->input->getFirstName(),
                'lastName'      =>$this->input->getLastName(),
                'phoneNumber'   =>$this->input->getPhoneNumber(),
                'password'      =>$hashedPassword,
            ]);
        }




        $phoneNumber = "963" . substr($this->input->getPhoneNumber(),1);

        $otpCode = MTNOtpIntegration::sendOtp($phoneNumber);

        setCatch("user_id_".$user->id , $otpCode);

        $response  = new UserRegisterOutput(  data:$user,
        message: SuccessMessages::getKey(SuccessMessages::$AccountCreated),
    );
        return $response->send_as_object();
   }
}
