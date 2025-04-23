<?php
namespace App\Http\Services\User\Auth\UserResetPasswordService\Logic;

use App\Http\Core\Const\Messages\ErrorMessages;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserResetPasswordServiceLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private UserResetPasswordServiceInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller();
    }


    public function execute (): ResponseModel {

        if (!Hash::check($this->input->getPassword(), Auth::user()->password)) {
            make_exception(ErrorMessages::getKey(ErrorMessages::$wrongPassword));
        }

        $userUpdateRepository = $this->repository->UserRepository()->updateRepository();

        $userUpdateRepository->update(['id'=>$this->input->getUserId()],[
            "password" => Hash::make($this->input->getNewPassword()),
        ]);

        // Output Of Logic

        $response  = new UserResetPasswordServiceOutput([] , '');
        return $response->send_as_array();
   }
}
