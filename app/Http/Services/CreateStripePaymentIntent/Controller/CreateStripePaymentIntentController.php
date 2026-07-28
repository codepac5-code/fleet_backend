<?php
namespace App\Http\Services\CreateStripePaymentIntent\Controller;

use App\Http\Services\CreateStripePaymentIntent\Logic\CreateStripePaymentIntentInput;
use App\Http\Services\CreateStripePaymentIntent\Logic\CreateStripePaymentIntentLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\CreateStripePaymentIntent\Request\CreateStripePaymentIntentRequest;

class CreateStripePaymentIntentController extends Controller
{
    public function __invoke(CreateStripePaymentIntentRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new CreateStripePaymentIntentInput($request->validated());

        $service = new CreateStripePaymentIntentLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}