<?php
namespace App\Http\Services\User\GetPaymentMethod\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetPaymentMethod\Logic\GetPaymentMethodInput;
use App\Http\Services\User\GetPaymentMethod\Logic\GetPaymentMethodLogic;
use App\Http\Services\User\GetPaymentMethod\Request\GetPaymentMethodRequest;
use Illuminate\Http\Request;

class GetPaymentMethodController extends Controller
{
    public function __invoke(Request $request)
    {
        $input   = new GetPaymentMethodInput($request->all());
        $service = new GetPaymentMethodLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
