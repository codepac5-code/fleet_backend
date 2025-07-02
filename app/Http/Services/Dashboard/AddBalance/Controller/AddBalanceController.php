<?php
namespace App\Http\Services\Dashboard\AddBalance\Controller;

use App\Http\Services\Dashboard\AddBalance\Logic\AddBalanceInput;
use App\Http\Services\Dashboard\AddBalance\Logic\AddBalanceLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\AddBalance\Request\AddBalanceRequest;

class AddBalanceController extends Controller
{
    public function __invoke(AddBalanceRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new AddBalanceInput($request->validated());

        $service = new AddBalanceLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute(); // send response..
    }
}