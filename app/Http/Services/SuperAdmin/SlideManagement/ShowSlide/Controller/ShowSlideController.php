<?php
namespace App\Http\Services\Dashboard\SlideManagement\ShowSlide\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SlideManagement\ShowSlide\Logic\ShowSlideInput;
use App\Http\Services\Dashboard\SlideManagement\ShowSlide\Logic\ShowSlideLogic;

class ShowSlideController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ShowSlideInput($request->validate());

        $service = new ShowSlideLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
