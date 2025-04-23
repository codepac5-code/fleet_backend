<?php
namespace App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Controller;

use App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Logic\DriverRattingIndexDataInput;
use App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Logic\DriverRattingIndexDataLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\RatingManagement\DriverRattingIndexData\Request\DriverRattingIndexDataRequest;

class DriverRattingIndexDataController extends Controller
{
    public function __invoke(DriverRattingIndexDataRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new DriverRattingIndexDataInput($request->validated());

        $service = new DriverRattingIndexDataLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

    }
}