<?php
namespace App\Http\Services\User\GetServices\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\User\GetServices\Logic\GetServicesInput;
use App\Http\Services\User\GetServices\Logic\GetServicesLogic;

class GetServicesController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new GetServicesInput($request->all());

        $service = new GetServicesLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        // return response()->json($result->getData());
        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
