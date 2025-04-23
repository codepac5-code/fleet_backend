<?php
namespace App\Http\Services\Dashboard\Transactions\ViewDriverPayout\Controller;

use App\Http\Services\Dashboard\Transactions\ViewDriverPayout\Logic\ViewDriverPayoutInput;
use App\Http\Services\Dashboard\Transactions\ViewDriverPayout\Logic\ViewDriverPayoutLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Transactions\ViewDriverPayout\Request\ViewDriverPayoutRequest;

class ViewDriverPayoutController extends Controller
{
    public function __invoke(ViewDriverPayoutRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewDriverPayoutInput($request->validated());

        $service = new ViewDriverPayoutLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}