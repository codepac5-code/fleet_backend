<?php
namespace App\Http\Services\Dashboard\BookingManagement\AssignToDriver\Controller;

use App\Http\Services\Dashboard\BookingManagement\AssignToDriver\Logic\AssignToDriverInput;
use App\Http\Services\Dashboard\BookingManagement\AssignToDriver\Logic\AssignToDriverLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\BookingManagement\AssignToDriver\Request\AssignToDriverRequest;

class AssignToDriverController extends Controller
{
    public function __invoke(AssignToDriverRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AssignToDriverInput($request->validated());

        $service = new AssignToDriverLogic($input); // call the service's logic

        // execute service and get result..
       return $service->execute();

    }
}
