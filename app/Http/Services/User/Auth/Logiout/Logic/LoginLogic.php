<?php
namespace App\Http\Services\User\Auth\Login\Logic;

use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Attribute;

class LoginLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private LoginInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }



    public function execute (): ResponseModel {

        $userReadRepository = $this->repository->UserRepository()->readRepository();
        $user = $userReadRepository->getByValue('phoneNumber' , $this->input->getPhoneNumber());

        if($user == null || !$user->is_registered) make_exception(ErrorMessages::getKey(ErrorMessages::$AccountAlreadyExists ,Attributes::User));


        if (!checkPassword($this->input->getPassword() , $user->password ))
        make_exception(ErrorMessages::getKey(''));

        $user['token']= getToken($user);

        $response  = new LoginOutput(data:$user,
        message:  SuccessMessages::getKey('')
        );
        return $response->send_as_array();
   }
}
