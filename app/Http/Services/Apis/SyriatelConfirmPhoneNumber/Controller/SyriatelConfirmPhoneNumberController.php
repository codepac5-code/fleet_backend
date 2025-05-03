<?php
namespace App\Http\Services\Apis\SyriatelConfirmPhoneNumber\Controller;

use App\Http\Services\Apis\SyriatelConfirmPhoneNumber\Logic\SyriatelConfirmPhoneNumberInput;
use App\Http\Services\Apis\SyriatelConfirmPhoneNumber\Logic\SyriatelConfirmPhoneNumberLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Apis\SyriatelConfirmPhoneNumber\Request\SyriatelConfirmPhoneNumberRequest;

class SyriatelConfirmPhoneNumberController extends Controller
{
    public function __invoke(SyriatelConfirmPhoneNumberRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new SyriatelConfirmPhoneNumberInput($request->validated());

        $service = new SyriatelConfirmPhoneNumberLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}