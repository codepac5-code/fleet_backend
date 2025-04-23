<?php
namespace App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Controller;

use App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Logic\UpdateCommissionsInput;
use App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Logic\UpdateCommissionsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\CommissionManagement\UpdateCommissions\Request\UpdateCommissionsRequest;

class UpdateCommissionsController extends Controller
{
    public function __invoke(UpdateCommissionsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new UpdateCommissionsInput($request->all());

        $service = new UpdateCommissionsLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}