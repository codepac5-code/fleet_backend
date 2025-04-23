<?php
namespace App\Http\Services\Dashboard\Home\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Home\Logic\HomeInput;
use App\Http\Services\Dashboard\Home\Logic\HomeLogic;
use App\Http\Services\Dashboard\Home\Request\HomeRequest;

class HomeController extends Controller
{

    public function __invoke(HomeRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new HomeInput($request->validated());

        $service = new HomeLogic($input); // call the service's logic

        // execute service and get result..
        $result = $service->execute();

        return $result;
    }
}