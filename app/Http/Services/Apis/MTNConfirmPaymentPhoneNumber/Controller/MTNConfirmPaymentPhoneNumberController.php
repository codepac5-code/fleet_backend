<?php
namespace App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Logic\MTNConfirmPaymentPhoneNumberInput;
use App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Logic\MTNConfirmPaymentPhoneNumberLogic;
use App\Http\Services\Apis\MTNConfirmPaymentPhoneNumber\Request\ConfirmPaymentPhoneNumberRequest;

class MTNConfirmPaymentPhoneNumberController extends Controller
{
    public function __invoke(ConfirmPaymentPhoneNumberRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new MTNConfirmPaymentPhoneNumberInput($request->validated());

        $service = new MTNConfirmPaymentPhoneNumberLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
