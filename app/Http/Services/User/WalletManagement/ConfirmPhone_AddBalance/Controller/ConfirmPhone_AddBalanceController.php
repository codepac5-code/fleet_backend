<?php
namespace App\Http\Services\User\WalletManagement\ConfirmPhone_AddBalance\Controller;

use App\Http\Services\User\WalletManagement\ConfirmPhone_AddBalance\Logic\ConfirmPhone_AddBalanceInput;
use App\Http\Services\User\WalletManagement\ConfirmPhone_AddBalance\Logic\ConfirmPhone_AddBalanceLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\WalletManagement\ConfirmPhone_AddBalance\Request\ConfirmPhone_AddBalanceRequest;

class ConfirmPhone_AddBalanceController extends Controller
{
    public function __invoke(ConfirmPhone_AddBalanceRequest $request)
    {

        $request->validated();
        // validate input data and pass it to the service..
        $service = new ConfirmPhone_AddBalanceLogic(); // call the service's logic

        // execute service and get result..
        $result = $service->{$request->input('type')}($request->validated());

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}