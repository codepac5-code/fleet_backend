<?php
namespace App\Http\Services\Dashboard\Settings\GetOfficeComission\Controller;

use App\Http\Services\Dashboard\Settings\GetOfficeComission\Logic\GetOfficeComissionInput;
use App\Http\Services\Dashboard\Settings\GetOfficeComission\Logic\GetOfficeComissionLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Settings\GetOfficeComission\Request\GetOfficeComissionRequest;

class GetOfficeComissionController extends Controller
{
    public function __invoke(GetOfficeComissionRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new GetOfficeComissionInput($request->validated());

        $service = new GetOfficeComissionLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();// send response..

          
    }
}