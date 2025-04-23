<?php
namespace App\Http\Services\User\Auth\Logiout\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Const\Messages\SuccessMessages;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class LogoutController extends Controller
{
    public function __invoke()
    {

        Auth::user()->token()->revoke();

        // execute service and get result..
        $result = new ResponseModel(
            data : [],
            message:SuccessMessages::getKey(SuccessMessages::$AccountCreated),
            status:200
        );

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
