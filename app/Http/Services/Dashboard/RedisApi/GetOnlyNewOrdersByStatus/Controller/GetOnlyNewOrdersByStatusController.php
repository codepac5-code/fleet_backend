<?php
namespace App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Controller;

use App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Logic\GetOnlyNewOrdersByStatusInput;
use App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Logic\GetOnlyNewOrdersByStatusLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RedisApi\GetOnlyNewOrdersByStatus\Request\GetOnlyNewOrdersByStatusRequest;

class GetOnlyNewOrdersByStatusController extends Controller
{
    public function __invoke(GetOnlyNewOrdersByStatusRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetOnlyNewOrdersByStatusInput($request->validated());

        $service = new GetOnlyNewOrdersByStatusLogic($input); // call the service's logic

        // execute service and get result..
       return $service->execute();  // send response..
    }
}