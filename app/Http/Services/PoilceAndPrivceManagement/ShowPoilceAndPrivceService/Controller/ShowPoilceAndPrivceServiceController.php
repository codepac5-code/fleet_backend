<?php
namespace App\Http\Services\PoilceAndPrivceManagement\ShowPoilceAndPrivceService\Controller;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\PoilceAndPrivceManagement\ShowPoilceAndPrivceService\Logic\ShowPoilceAndPrivceServiceInput;
use App\Http\Services\PoilceAndPrivceManagement\ShowPoilceAndPrivceService\Logic\ShowPoilceAndPrivceServiceLogic;

class ShowPoilceAndPrivceServiceController extends Controller
{
    public function __invoke(Request $request)
    {
        // validate input data and pass it to the service..
        $input = new ShowPoilceAndPrivceServiceInput($request->all());

        $service = new ShowPoilceAndPrivceServiceLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return SendResponse::sendSuccessResponse($result); // send response..
    }
}
