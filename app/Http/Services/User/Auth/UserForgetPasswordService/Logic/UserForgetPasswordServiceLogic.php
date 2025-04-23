<?php
namespace App\Http\Services\User\Auth\UserForgetPasswordService\Logic;

use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\Hash;

class UserForgetPasswordServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UserForgetPasswordServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


        public function execute ( ) : ResponseModel {

            $userReadRepository = $this->repository->UserRepository()->readRepository();
            $user = $userReadRepository->getByValue('phoneNumber',$this->input->getPhoneNumber());

            if($user == null){
                make_exception(ErrorMessages::getKey(ErrorMessages::$notExait),422);
            }

            $code = getCatch("user_id_".$user->id);
            if ($code == null || $code != $this->input->getCode()){
                make_exception(ErrorMessages::getKey(ErrorMessages::$endOtp),422);
            }

            $userReadRepository = $this->repository->UserRepository()->updateRepository();

            $userReadRepository->update(["phoneNumber"=>$this->input->getPhoneNumber()],[
                "password" => Hash::make($this->input->getPassword()),
            ]);


        // Output Of Logic
        $response  = new UserForgetPasswordServiceOutput([] , '');
        return $response->send_as_object();
   }
}
