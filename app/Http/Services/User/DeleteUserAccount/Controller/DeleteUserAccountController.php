<?php
namespace App\Http\Services\User\DeleteUserAccount\Controller;

use App\Http\Services\User\DeleteUserAccount\Logic\DeleteUserAccountInput;
use App\Http\Services\User\DeleteUserAccount\Logic\DeleteUserAccountLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\DeleteUserAccount\Request\DeleteUserAccountRequest;

class DeleteUserAccountController extends Controller
{
    public function __invoke(DeleteUserAccountRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DeleteUserAccountInput($request->validated());

        $service = new DeleteUserAccountLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}