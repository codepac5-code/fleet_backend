<?php
namespace App\Http\Services\User\Auth\UserCheckOtpService\Logic;

use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class UserCheckOtpServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UserCheckOtpServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        if (getCatch("user_id_".$this->input->getUserId()) == null) {
            make_exception(ErrorMessages::getKey(ErrorMessages::$endOtp),422);
        }

        $userReadRepository = $this->repository->UserRepository()->readRepository();
        $user = $userReadRepository->find($this->input->getUserId());

        if($user == null){
            make_exception(ErrorMessages::getKey(ErrorMessages::$notExait),422);
        }

        if (!$user->is_registered) {
            $user->is_registered = 1;
            $user->save();
        }


        $response  = new UserCheckOtpServiceOutput($user , '');
        return $response->send_as_array();
   }
}
