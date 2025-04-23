<?php
namespace App\Http\Services\User\Auth\UserResetPasswordService\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\Auth\UserResetPasswordService\Logic\UserResetPasswordServiceInput;
use App\Http\Services\User\Auth\UserResetPasswordService\Logic\UserResetPasswordServiceLogic;
use App\Http\Services\User\Auth\UserResetPasswordService\Request\UserResetPasswordServiceRequest;

class UserResetPasswordServiceController extends Controller
{
    public function __invoke(UserResetPasswordServiceRequest $request)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        $data['userId'] = Auth::id();
        $input = new UserResetPasswordServiceInput($data);

        $service = new UserResetPasswordServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
