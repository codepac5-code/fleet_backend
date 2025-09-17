<?php
namespace App\Http\Services\User\Auth\Login\Logic;

use Attribute;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\Const\Messages\Attributes;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

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

        if($user == null || !$user->is_registered || $user->dialCode == $this->input->getDialCode()
          ) make_exception(__('messages.account_not_found_phoneNumber'));
        // || 
        //ErrorMessages::getKey(ErrorMessages::$AccountAlreadyExists ,Attributes::User)
        if ( $user->isActive == false){
            make_exception('تم تقييد حسابك من قبل الشركة ، يرجى المراجعة');
        }

        if (!checkPassword($this->input->getPassword() , $user->password )){
            make_exception(__('messages.incorect_password'));//ErrorMessages::getKey('')
        }

        $user['token']= getToken($user);

        $response  = new LoginOutput(data:$user,
        message:  __('messages.login_successfully')
        );
        return $response->send_as_array();
   }
}
