<?php
namespace App\Http\Services\Dashboard\GetHomeStatistic\Controller;

use App\Http\Services\Dashboard\GetHomeStatistic\Logic\GetHomeStatisticInput;
use App\Http\Services\Dashboard\GetHomeStatistic\Logic\GetHomeStatisticLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\GetHomeStatistic\Request\GetHomeStatisticRequest;

class GetHomeStatisticController extends Controller
{
    public function __invoke(GetHomeStatisticRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetHomeStatisticInput($request->validated());

        $service = new GetHomeStatisticLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}