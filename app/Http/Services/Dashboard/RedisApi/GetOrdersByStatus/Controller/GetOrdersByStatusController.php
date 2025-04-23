<?php
namespace App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Controller;

use App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Logic\GetOrdersByStatusInput;
use App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Logic\GetOrdersByStatusLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RedisApi\GetOrdersByStatus\Request\GetOrdersByStatusRequest;

class GetOrdersByStatusController extends Controller
{
    public function __invoke(GetOrdersByStatusRequest $request)
    {
        // validate input data and pass it to the service..


        $data = $request->validated();
        $data['page'] = request()->get('page', 1);
        

        $input = new GetOrdersByStatusInput($data);

        $service = new GetOrdersByStatusLogic($input); // call the service's logic

        // execute service and get result..
        return  $service->execute(); // send response..
    }
}