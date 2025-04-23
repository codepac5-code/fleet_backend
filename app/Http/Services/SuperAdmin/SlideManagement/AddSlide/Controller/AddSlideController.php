<?php
namespace App\Http\Services\Dashboard\SlideManagement\AddSlide\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SlideManagement\AddSlide\Logic\AddSlideInput;
use App\Http\Services\Dashboard\SlideManagement\AddSlide\Logic\AddSlideLogic;
use App\Http\Services\Dashboard\SlideManagement\AddSlide\Request\AddSlideRequest;

class AddSlideController extends Controller
{
    public function __invoke(AddSlideRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AddSlideInput($request->validate());

        $service = new AddSlideLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
