<?php
namespace App\Http\Services\PoilceAndPrivceManagement\EditePoilceAndPrivceService\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\PoilceAndPrivceManagement\EditePoilceAndPrivceService\Logic\EditePoilceAndPrivceServiceInput;
use App\Http\Services\PoilceAndPrivceManagement\EditePoilceAndPrivceService\Logic\EditePoilceAndPrivceServiceLogic;

class EditePoilceAndPrivceServiceController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new EditePoilceAndPrivceServiceInput($request->validate());

        $service = new EditePoilceAndPrivceServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
