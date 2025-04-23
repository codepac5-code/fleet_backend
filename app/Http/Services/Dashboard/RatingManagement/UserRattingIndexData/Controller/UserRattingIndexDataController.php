<?php
namespace App\Http\Services\Dashboard\RatingManagement\UserRattingIndexData\Controller;

use App\Http\Services\Dashboard\RatingManagement\UserRattingIndexData\Logic\UserRattingIndexDataInput;
use App\Http\Services\Dashboard\RatingManagement\UserRattingIndexData\Logic\UserRattingIndexDataLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RatingManagement\UserRattingIndexData\Request\UserRattingIndexDataRequest;

class UserRattingIndexDataController extends Controller
{
    public function __invoke(UserRattingIndexDataRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new UserRattingIndexDataInput($request->validated());

        $service = new UserRattingIndexDataLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

        // return SendResponse::sendSuccessResponse($result); // send response..
    }
}