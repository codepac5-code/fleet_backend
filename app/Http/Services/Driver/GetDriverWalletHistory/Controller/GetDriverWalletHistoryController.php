<?php
namespace App\Http\Services\Driver\GetDriverWalletHistory\Controller;

use App\Http\Services\Driver\GetDriverWalletHistory\Logic\GetDriverWalletHistoryInput;
use App\Http\Services\Driver\GetDriverWalletHistory\Logic\GetDriverWalletHistoryLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Driver\GetDriverWalletHistory\Request\GetDriverWalletHistoryRequest;

class GetDriverWalletHistoryController extends Controller
{
    public function __invoke(GetDriverWalletHistoryRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetDriverWalletHistoryInput($request->validated());

        $service = new GetDriverWalletHistoryLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}