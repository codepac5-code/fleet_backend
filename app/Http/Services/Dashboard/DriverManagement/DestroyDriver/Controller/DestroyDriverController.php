<?php
namespace App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Logic\DestroyDriverInput;
use App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Logic\DestroyDriverLogic;
use App\Http\Services\Dashboard\DriverManagement\DestroyDriver\Request\DestroyDriverRequest;

class DestroyDriverController extends Controller
{
    public function __invoke(DestroyDriverRequest $request , $id)
    {
        // validate input data and pass it to the service..
        $data = $request->validated();
        $input = new DestroyDriverInput( $data);

        $service = new DestroyDriverLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return $result; // send response..
    }
}
