<?php
namespace App\Http\Services\User\Auth\UserSendOtpServiceService\Logic;

use App\Helper\integrations\MTNOtpIntegration;
use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Repositories\UserRepositories\UserReadRepository;

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
        make_exception(ErrorMessages::getKey(ErrorMessages::$notExait));
    }


    $phoneNumber = "963" . substr($this->input->getPhoneNumber(),1);

    $otpCode = MTNOtpIntegration::sendOtp($phoneNumber);

    setCatch("user_id_".$user->id , $otpCode);

        $response  = new UserSendOtpServiceServiceOutput([
            "phoneNumber" => $this->input->getPhoneNumber(),
            "userId" => $user->id
        ] , '');

        return $response->send_as_array();
   }
}
