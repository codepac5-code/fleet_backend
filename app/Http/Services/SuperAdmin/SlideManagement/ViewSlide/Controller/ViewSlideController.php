<?php
namespace App\Http\Services\Dashboard\SlideManagement\ViewSlide\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SlideManagement\ViewSlide\Logic\ViewSlideInput;
use App\Http\Services\Dashboard\SlideManagement\ViewSlide\Logic\ViewSlideLogic;

class ViewSlideController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewSlideInput($request->validate());

        $service = new ViewSlideLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
