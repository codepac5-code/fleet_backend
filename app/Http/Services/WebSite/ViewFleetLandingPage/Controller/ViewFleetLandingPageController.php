<?php
namespace App\Http\Services\WebSite\ViewFleetLandingPage\Controller;

use App\Http\Services\WebSite\ViewFleetLandingPage\Logic\ViewFleetLandingPageInput;
use App\Http\Services\WebSite\ViewFleetLandingPage\Logic\ViewFleetLandingPageLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\WebSite\ViewFleetLandingPage\Request\ViewFleetLandingPageRequest;

class ViewFleetLandingPageController extends Controller
{
    public function __invoke(ViewFleetLandingPageRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewFleetLandingPageInput($request->validated());

        $service = new ViewFleetLandingPageLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return $result;
        //return SendResponse::sendSuccessResponse($result); // send response..
    }
}