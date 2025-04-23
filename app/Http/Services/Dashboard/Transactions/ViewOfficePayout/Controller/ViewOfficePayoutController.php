<?php
namespace App\Http\Services\Dashboard\Transactions\ViewOfficePayout\Controller;

use App\Http\Services\Dashboard\Transactions\ViewOfficePayout\Logic\ViewOfficePayoutInput;
use App\Http\Services\Dashboard\Transactions\ViewOfficePayout\Logic\ViewOfficePayoutLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Transactions\ViewOfficePayout\Request\ViewOfficePayoutRequest;

class ViewOfficePayoutController extends Controller
{
    public function __invoke(ViewOfficePayoutRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewOfficePayoutInput($request->validated());

        $service = new ViewOfficePayoutLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}