<?php
namespace App\Http\Services\Apis\StripePayment\Controller;

use App\Http\Services\Apis\StripePayment\Logic\StripePaymentInput;
use App\Http\Services\Apis\StripePayment\Logic\StripePaymentLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Apis\StripePayment\Request\StripePaymentRequest;

class StripePaymentController extends Controller
{
    public function __invoke(StripePaymentRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new StripePaymentInput($request->validated());

        $service = new StripePaymentLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}