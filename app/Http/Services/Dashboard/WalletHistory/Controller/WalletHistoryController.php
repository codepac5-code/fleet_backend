<?php
namespace App\Http\Services\Dashboard\WalletHistory\Controller;

use App\Http\Services\Dashboard\WalletHistory\Logic\WalletHistoryInput;
use App\Http\Services\Dashboard\WalletHistory\Logic\WalletHistoryLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\WalletHistory\Request\WalletHistoryRequest;

class WalletHistoryController extends Controller
{
    public function __invoke(WalletHistoryRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new WalletHistoryInput($request->validated());

        $service = new WalletHistoryLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}