<?php
namespace App\Http\Services\Dashboard\DriverJobApplicationsMangement\DriverJobApplicationList\Controller;

use App\Http\Services\Dashboard\DriverJobApplicationsMangement\DriverJobApplicationList\Logic\DriverJobApplicationListInput;
use App\Http\Services\Dashboard\DriverJobApplicationsMangement\DriverJobApplicationList\Logic\DriverJobApplicationListLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\DriverJobApplicationsMangement\DriverJobApplicationList\Request\DriverJobApplicationListRequest;

class DriverJobApplicationListController extends Controller
{
    public function __invoke(DriverJobApplicationListRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DriverJobApplicationListInput($request->validated());

        $service = new DriverJobApplicationListLogic($input); // call the service's logic

        // execute service and get result..
        return $result = $service->execute();

    }
}
