<?php
namespace App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Controller;

use App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Logic\ViewOfficeDriversInput;
use App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Logic\ViewOfficeDriversLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\DriverManagement\ViewOfficeDrivers\Request\ViewOfficeDriversRequest;

class ViewOfficeDriversController extends Controller
{
    public function __invoke(ViewOfficeDriversRequest $request )
    {

        // validate input data and pass it to the service..
        $input = new ViewOfficeDriversInput($request->validated());

        $service = new ViewOfficeDriversLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..

    }
}