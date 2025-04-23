<?php
namespace App\Http\Services\User\ResearchOnDriver\Controller;

use App\Http\Services\User\ResearchOnDriver\Logic\ResearchOnDriverInput;
use App\Http\Services\User\ResearchOnDriver\Logic\ResearchOnDriverLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\ResearchOnDriver\Request\ResearchOnDriverRequest;

class ResearchOnDriverController extends Controller
{
    public function __invoke(ResearchOnDriverRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ResearchOnDriverInput($request->validated());

        $service = new ResearchOnDriverLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}