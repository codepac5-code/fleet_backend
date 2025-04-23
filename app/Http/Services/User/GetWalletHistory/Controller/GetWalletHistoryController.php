<?php
namespace App\Http\Services\User\GetWalletHistory\Controller;

use App\Http\Services\User\GetWalletHistory\Logic\GetWalletHistoryInput;
use App\Http\Services\User\GetWalletHistory\Logic\GetWalletHistoryLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetWalletHistory\Request\GetWalletHistoryRequest;

class GetWalletHistoryController extends Controller
{
    public function __invoke(GetWalletHistoryRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetWalletHistoryInput($request->validated());

        $service = new GetWalletHistoryLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}