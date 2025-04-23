<?php
namespace App\Http\Services\Dashboard\Transactions\ViewPayments\Controller;

use App\Http\Services\Dashboard\Transactions\ViewPayments\Logic\ViewPaymentsInput;
use App\Http\Services\Dashboard\Transactions\ViewPayments\Logic\ViewPaymentsLogic;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Services\Dashboard\Transactions\ViewPayments\Request\ViewPaymentsRequest;

class ViewPaymentsController extends Controller
{
    public function __invoke(ViewPaymentsRequest $request)
    {
        // validate input data and pass it to the service..
        $input = new ViewPaymentsInput($request->validated());

        $service = new ViewPaymentsLogic($input); // call the service's logic

        // execute service and get result..
        return $service->execute();

    }
}