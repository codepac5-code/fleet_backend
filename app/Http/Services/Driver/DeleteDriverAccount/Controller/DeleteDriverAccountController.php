<?php
namespace App\Http\Services\Driver\DeleteDriverAccount\Controller;

use App\Http\Services\Driver\DeleteDriverAccount\Logic\DeleteDriverAccountInput;
use App\Http\Services\Driver\DeleteDriverAccount\Logic\DeleteDriverAccountLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\DeleteDriverAccount\Request\DeleteDriverAccountRequest;

class DeleteDriverAccountController extends Controller
{
    public function __invoke(DeleteDriverAccountRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DeleteDriverAccountInput($request->validated());

        $service = new DeleteDriverAccountLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}