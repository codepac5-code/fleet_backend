<?php
namespace App\Http\Services\User\WalletManagement\AddBalanceByPaymentMethod\Controller;

use App\Http\Services\User\WalletManagement\AddBalanceByPaymentMethod\Logic\AddBalanceByPaymentMethodInput;
use App\Http\Services\User\WalletManagement\AddBalanceByPaymentMethod\Logic\AddBalanceByPaymentMethodLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\WalletManagement\AddBalanceByPaymentMethod\Request\AddBalanceByPaymentMethodRequest;

class AddBalanceByPaymentMethodController extends Controller
{
    public function __invoke(AddBalanceByPaymentMethodRequest $request)
    {
        $request->validated();
        // validate input data and pass it to the service..
        $service = new AddBalanceByPaymentMethodLogic(); // call the service's logic

        // execute service and get result..
        $result = $service->{$request->input('type')}($request);

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}