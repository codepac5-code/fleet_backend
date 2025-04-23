<?php
namespace App\Http\Services\Dashboard\SlideManagement\DeleteSlide\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\SlideManagement\DeleteSlide\Logic\DeleteSlideInput;
use App\Http\Services\Dashboard\SlideManagement\DeleteSlide\Logic\DeleteSlideLogic;

class DeleteSlideController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new DeleteSlideInput($request->validate());

        $service = new DeleteSlideLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
